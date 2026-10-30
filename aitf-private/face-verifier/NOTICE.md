# Face model sources

Models are pinned to OpenCV Zoo commit
`48b10fa505aeb51fbd127fc60a5c660040e823b7` and downloaded by `download_models.py`.

* [YuNet 2023mar](https://github.com/opencv/opencv_zoo/tree/48b10fa505aeb51fbd127fc60a5c660040e823b7/models/face_detection_yunet): MIT license, reproduced in `models/YuNet-LICENSE`.
* [SFace 2021dec](https://github.com/opencv/opencv_zoo/tree/48b10fa505aeb51fbd127fc60a5c660040e823b7/models/face_recognition_sface): Apache 2.0, reproduced in `models/SFace-LICENSE`.

The application-specific worker and motion checks are new code. Upstream model
weights were not modified. ONNX binaries are excluded from Git and verified by
SHA-256 when downloaded and loaded.
