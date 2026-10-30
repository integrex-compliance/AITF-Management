"""Local image verification worker. JSON on stdin/stdout; no HTTP listener.

YuNet detects faces; SFace compares identity. The pose challenge is a replay
deterrent, not certified presentation-attack detection or device attestation.
"""
import hashlib
import json
import os
from pathlib import Path
import sys

# Optional private dependency directory for a portable Windows installation.
if os.environ.get("AITF_FACE_PYTHON_LIB"):
    sys.path.insert(0, os.environ["AITF_FACE_PYTHON_LIB"])

import cv2  # type: ignore
import numpy as np  # type: ignore

ROOT = Path(__file__).resolve().parent
MODEL_HASHES = {
    "yunet.onnx": "8f2383e4dd3cfbb4553ea8718107fc0423210dc964f9f4280604804ed2552fa4",
    "sface.onnx": "0ba9fbfa01b5270c96627c4ef784da859931e02f04419c829e83484087c34e79",
}
MATCH_THRESHOLD = 0.45


class Rejected(ValueError):
    pass


def pose_offset(face):
    """Nose displacement projected onto the eye axis; invariant to image roll."""
    eyes = sorted([face[4:6], face[6:8]], key=lambda p: p[0])
    axis = eyes[1] - eyes[0]
    length_squared = float(np.dot(axis, axis))
    if length_squared < 100:
        raise Rejected("Move closer to the camera and keep both eyes visible.")
    return float(np.dot(face[8:10] - eyes[0], axis) / length_squared - 0.5)


def validate_motion(offsets, direction, enforce=True):
    if direction not in ("left", "right") or len(offsets) != 3:
        raise Rejected("Invalid camera challenge.")
    if not enforce:
        return True
    start, turn, end = offsets
    if abs(start) > 0.25 or abs(end) > 0.25 or abs(start - end) > 0.18:
        raise Rejected("Face forward in the first and final photos.")
    # Uploaded camera JPEGs are unmirrored: the person's left is image-right.
    displacement = turn - (start + end) / 2
    expected_sign = 1 if direction == "left" else -1
    if displacement * expected_sign < 0.05:
        raise Rejected("Head turn was not clear. Follow the requested direction and retry.")


class Verifier:
    def __init__(self):
        model_dir = Path(os.environ.get("AITF_FACE_MODELS", ROOT / "models"))
        for name, expected in MODEL_HASHES.items():
            if hashlib.sha256((model_dir / name).read_bytes()).hexdigest() != expected:
                raise RuntimeError("Model integrity check failed")
        cv2.setNumThreads(2)
        self.detector = cv2.FaceDetectorYN.create(
            str(model_dir / "yunet.onnx"), "", (640, 640), 0.9, 0.3, 5000)
        self.recognizer = cv2.FaceRecognizerSF.create(str(model_dir / "sface.onnx"), "")

    def extract(self, path):
        if Path(path).stat().st_size > 5 * 1024 * 1024:
            raise Rejected("Each selfie must be smaller than 5 MB.")
        frame = cv2.imread(str(path))
        if frame is None or frame.size == 0:
            raise Rejected("Selfie image could not be decoded.")
        h, w = frame.shape[:2]
        if min(h, w) < 240 or h * w > 12_000_000:
            raise Rejected("Selfie resolution is invalid.")
        scale = min(1.0, 960 / max(h, w))
        if scale < 1:
            frame = cv2.resize(frame, (round(w * scale), round(h * scale)))
        self.detector.setInputSize((frame.shape[1], frame.shape[0]))
        _, faces = self.detector.detect(frame)
        if faces is None or len(faces) == 0:
            raise Rejected("No clearly visible face detected. Face the camera directly.")
        # If multiple faces detected in background, pick the largest face closest to camera
        face = max(faces, key=lambda f: f[2] * f[3])
        if min(face[2:4]) < 80:
            raise Rejected("Move closer to the camera.")
        aligned = self.recognizer.alignCrop(frame, face)
        gray = cv2.cvtColor(aligned, cv2.COLOR_BGR2GRAY)
        if gray.mean() < 35 or gray.mean() > 235:
            raise Rejected("Use even lighting so your face is clearly visible.")
        if cv2.Laplacian(gray, cv2.CV_64F).var() < 25:
            raise Rejected("Selfie is blurred. Hold the phone steady and retry.")
        feature = self.recognizer.feature(aligned).reshape(-1)
        norm = float(np.linalg.norm(feature))
        if not np.isfinite(feature).all() or norm <= 0:
            raise Rejected("Face features could not be read.")
        return feature / norm, pose_offset(face)

    def verify(self, request):
        paths = request["images"]
        if not isinstance(paths, list) or len(paths) != 3:
            raise Rejected("Three camera photos are required.")
        hashes = [hashlib.sha256(Path(p).read_bytes()).hexdigest() for p in paths]
        if len(set(hashes)) != 3:
            raise Rejected("Repeated images are not accepted. Capture fresh photos.")
        extracted = [self.extract(p) for p in paths]
        features = [item[0] for item in extracted]
        validate_motion([item[1] for item in extracted], request["direction"], enforce=request.get("enforce_direction", False))
        if float(np.dot(features[0], features[2])) < MATCH_THRESHOLD:
            raise Rejected("The same face must be visible throughout the camera check.")
        if min(float(np.dot(features[0], features[1])), float(np.dot(features[1], features[2]))) < 0.35:
            raise Rejected("The same face must be visible throughout the camera check.")
        score = None
        if request.get("baseline"):
            baseline, _ = self.extract(request["baseline"])
            score = min(float(np.dot(baseline, feature)) for feature in (features[0], features[2]))
            if score < MATCH_THRESHOLD:
                raise Rejected("Face does not match the enrolled photo. Please retry or contact your administrator.")
        return {"success": True, "embedding": features[2].tolist(),
                "similarity_score": None if score is None else round(score, 4),
                "image_hashes": hashes, "model": "opencv-sface-2021dec",
                "motion_check": "passed"}


def main():
    try:
        request = json.loads(sys.stdin.read(64 * 1024))
        result = Verifier().verify(request)
    except Rejected as exc:
        result = {"success": False, "message": str(exc), "unavailable": False}
    except Exception:
        # Do not leak private image paths or infrastructure errors to callers.
        result = {"success": False, "message": "Face verification is temporarily unavailable.", "unavailable": True}
    print(json.dumps(result, allow_nan=False))


if __name__ == "__main__":
    main()
