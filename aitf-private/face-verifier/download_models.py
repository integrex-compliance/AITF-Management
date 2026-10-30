"""Download pinned upstream models and verify their SHA-256 checksums."""
import hashlib
from pathlib import Path
import urllib.request

COMMIT = "48b10fa505aeb51fbd127fc60a5c660040e823b7"
ROOT = Path(__file__).resolve().parent
MODELS = [
    ("face_detection_yunet", "face_detection_yunet_2023mar.onnx", "yunet.onnx",
     "8f2383e4dd3cfbb4553ea8718107fc0423210dc964f9f4280604804ed2552fa4"),
    ("face_recognition_sface", "face_recognition_sface_2021dec.onnx", "sface.onnx",
     "0ba9fbfa01b5270c96627c4ef784da859931e02f04419c829e83484087c34e79"),
]

if __name__ == "__main__":
    (ROOT / "models").mkdir(exist_ok=True)
    for directory, original, name, expected in MODELS:
        target = ROOT / "models" / name
        if not target.exists() or hashlib.sha256(target.read_bytes()).hexdigest() != expected:
            url = f"https://media.githubusercontent.com/media/opencv/opencv_zoo/{COMMIT}/models/{directory}/{original}"
            with urllib.request.urlopen(url, timeout=120) as response:
                content = response.read(50 * 1024 * 1024)
            if hashlib.sha256(content).hexdigest() != expected:
                raise RuntimeError(f"Checksum mismatch for {name}")
            target.write_bytes(content)
        print(f"Verified {name}")
