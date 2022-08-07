@extends('layouts.main')
@section('title', 'Presensi')
@section('main')

    <div class="row">
        <div class="col-md-4 d-grid gap-2 pt-1">
            <button class="btn btn-primary btn-lg btn-block mt-md-5" onclick="showQrScannerModal()">
                <i class="bi bi-qr-code"></i>
                Pindai QR
            </button>

            <button class="btn btn-light btn-lg btn-block" onclick="showPassKeyFormModal()">Masukkan Kode</button>

        </div>
        <div class="col-md-8">
            <h3 class="mt-3 h4">Akan Datang</h3>
            @if ($upcomingMeetings->count() > 0)
                @include('presences.components._upcomingMeetingsDiv')
            @else
                @include('layouts.components.alert', [
                    'class' => 'success',
                    'message' => 'Anda bisa bersantai🎉. belum ada agenda yang akan datang.',
                ])
            @endif

        </div>
    </div>


    <div class="d-flex align-items-center pt-3 pb-2 mb-3 mt-5">
        <h3 class="h4">Semua Agenda</h3>
        <button class="btn btn-success mx-4" data-bs-toggle="modal" data-bs-target="#meetingModal">Buat Agenda</button>
    </div>

    @if ($allMeetings->count() > 0)
        @include('presences.components._meetingsTableDiv', ['mettings' => $allMeetings])
    @else
        @include('layouts.components.alert', [
            'class' => 'warning',
            'message' =>
                'Anda belum memiliki agenda. silahkan hubungi penyelenggara agenda atau <a href="#">buat agenda baru</a> anda.',
        ])
    @endif





    @include('presences.components._meetingFormModal')

    @include('presences.components._meetingAttendancesModal')

    <div class="modal hide fade" tabindex="-1" id="passKeyFormModal" aria-labelledby="passKeyFormModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passKeyFormModalTitle">Masukkan Kode Presensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('presences.update-attendance') }}" id="passKeyForm">
                        <div class="form-floating mb-3">
                            <input name="pass_key" id="pass_key" class="form-control fs-3 pt-5 pb-5"
                                placeholder="Kode Presensi">
                            <label for="pass_key">Kode Presensi</label>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="passKeyForm" class="btn btn-success">Kirim</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal hide fade" tabindex="-1" id="qrScannerModal" aria-labelledby="qrScannerModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrScannerModalTitle">Arahkan ke kode QR</h5>
                    <button type="button" class="btn-close" onClick="hideScannerModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('layouts.components.alert', [
                        'class' => 'warning',
                        'message' => 'Mohon izinkan akses kamera',
                    ])
                    <div class="d-flex justify-content-center">
                        <div id="scannerPreviewContainer" class="ratio ratio-1x1 overflow-hidden mx-5">
                            <div>
                                <div class="d-flex justify-content-center" style="width: 100%; height: 100%;">
                                    <i class="bi bi-camera-video-off" style="font-size: 6em"></i>
                                    <video id="preview" class="d-none"></video>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-link" onclick="setScannerCamera()" data-bs-toggle="tooltip"
                        data-bs-title="Ganti Camera">
                        <i class="bi bi-phone-flip fs-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>





@endsection


@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"
        integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('scripts')
    @include('presences.components._scripts')
    <script src="{{ URL::asset('assets/js/instascan.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"
        integrity="sha512-57oZ/vW8ANMjR/KQ6Be9v/+/h6bq9/l3f0Oc7vn6qMqyhvPd1cvKBRWWpzu0QoneImqr2SkmO4MSqU+RpHom3Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script type="text/javascript">
        let scanner = null;
        let activeCameraId = null;
        let camera_devices = null;
        let qrScannerModal = null;
        let qrScannerModalAlert = null;

        $(function() {
            qrScannerModal = $('#qrScannerModal');
            qrScannerModalAlert = qrScannerModal.find('[role="alert"]');
        });

        function setQrScannerModalAlert(message, clss) {
            qrScannerModalAlert.html(message);
            qrScannerModalAlert.removeClass();

            qrScannerModalAlert.addClass('alert alert-' + clss);

            if (clss === 'success') {

                qrScannerModalAlert.effect("shake", {
                    direction: "left",
                    times: 1,
                    distance: 10
                });
            } else {
                qrScannerModalAlert.effect("shake", {
                    direction: "up",
                    times: 4,
                    distance: 10
                });
            }

        }


        function showPassKeyFormModal() {
            $('#passKeyFormModal').modal('show');
        }

        function showQrScannerModal() {
            initInstascan();
            qrScannerModal.modal('show');
        }

        function hideScannerModal() {
            qrScannerModal.modal('hide');
            scanner.stop();
        }

        function isMirrorCamera(cameraName) {
          const lowerCameraName = cameraName.toLowerCase();
          const isCameraFront = lowerCameraName.includes("front");
          const isWebCam = lowerCameraName.includes("webcam") || lowerCameraName.includes('web cam') || lowerCameraName.includes('web-cam');

          return isCameraFront || isWebCam;
        }

        function setScannerCamera() {
            scanner.stop();
            const cameraActive = camera_devices[getCameraId()];
            scanner.mirror = isMirrorCamera(cameraActive.name);
            return scanner.start(cameraActive);
        }

        function getCameraId() {
            if (activeCameraId === null) {
                activeCameraId = parseInt(localStorage.getItem("lastCameraId")) || camera_devices.length - 1;
            } else if (activeCameraId == camera_devices.length - 1) {
                activeCameraId = 0;
            } else {
                activeCameraId++;
            }

            localStorage.setItem("lastCameraId", activeCameraId);
            return activeCameraId;
        }

        function isContentValid(string) {
            let url;

            try {
                url = new URL(string);
            } catch (_) {
                return false;
            }

            return url.protocol + '//' + url.host === '{{ url('/') }}';
        }

        function initInstascan() {
            if (!scanner) {
                scanner = new Instascan.Scanner({
                    video: document.getElementById('preview')
                });

                scanner.addListener('scan', function(content) {
                    if (isContentValid(content)) {
                        window.location.replace(content);
                    } else {
                        setQrScannerModalAlert('Kode QR tidak valid, silahkan coba lagi.', 'danger');
                    }
                });
            }

            Instascan.Camera.getCameras().then(function(cameras) {
                if (cameras.length > 0) {
                    const container = qrScannerModal.find('#scannerPreviewContainer');
                    const video = qrScannerModal.find('video');

                    if (!camera_devices) {
                        camera_devices = cameras;
                        setScannerCamera();
                    }

                    setQrScannerModalAlert('Silahkan arahkan kamera pada kode QR', 'success');
                    container.find('i').addClass('d-none');
                    video.removeClass('d-none');

                } else {
                    setQrScannerModalAlert('Tidak dapat menemukan camera', 'danger');
                }
            }).catch(e => {
                setQrScannerModalAlert('Error: ' + e.message, 'danger');
            });
        }
    </script>
@endsection
