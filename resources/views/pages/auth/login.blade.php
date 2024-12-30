<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplikasi Inventory Karung</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('templates/dist/css/adminlte.min.css') }}">
    <style>
        .login-logo .logo-container {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-logo .logo-container img {
            margin-bottom: 10px;
            /* memberi jarak antara gambar dan teks */
        }

        .login-logo .logo-container a {
            font-size: 24px;
            /* ukuran font untuk teks */
            font-weight: bold;
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <div class="logo-container">
                <img src="{{ asset('img/logo.jpg') }}" alt="" style="height: 100px">
                <a href="#"><b>KARTOCK</b>-v1</a>
            </div>
        </div>
        <!-- /.login-logo -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Masuk Untuk Menggunakan Aplikasi</p>

                <form action="/login" method="post">
                    @csrf
                    @method('POST')
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            placeholder="Email">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Captcha -->
                    <div class="mb-3">
                        <label for="captcha">What is {{ $num1 }} + {{ $num2 }}?</label>
                        <input type="text" name="captcha" required class="form-control form-control-sm">
                        @error('captcha')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>



                    <div class="row">
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary float-end">Sign In</button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="{{ asset('templates/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('templates/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('templates/dist/js/adminlte.min.js') }}"></script>
</body>

</html>
