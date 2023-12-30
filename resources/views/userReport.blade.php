<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>

    <link rel="stylesheet" href="ReportStyle.css" type="text/css">
    {{-- external css doesn't work .. maybe you have to link it --}}

</head>

<body>
    <table class="w-full">
        <tr>
            <td>
                {{-- <img src="data:image/svg+xml;base64,<?php echo base64_encode(file_get_contents('http://127.0.0.1:8000/storage/app/MedHubLogo.jpg')); ?>" width="100"> this take alot of time to load --}}
                <img src="data:image;base64,<?php echo base64_encode(file_get_contents('MedHubLogo.png')); ?>" width="140">
                {{-- <img src="MedHubLogo.png" alt="MedHub Logo" width="100" /> --}}
            </td>
            <td class="w-half">
                <h1>MedHub</h1>
                <h3>Report To : {{ $data['user'] }}</h3>
                <h4> From {{ $data['from'] }} to {{ $data['to'] }}</h4>
            </td>
        </tr>
    </table>

    <br>
    <hr>
    {{-- TODO: generlize between admin and user report --}}
    {{-- TODO: https://codepen.io/MarkBoots/pen/YzvPRKr --}}
    <div>
        @foreach ($data as $key => $item)
            @if (!in_array($key, ['from', 'to', 'user', 'carts', 'carts chart']))
                <div class = 'element'>
                    <div class = 'key'> {{ $key }} :</div>
                    {{ $item }}
                    @if ($key === 'total payment')
                        S.P
                    @endif
                </div>
            @endif
        @endforeach

        {{-- bills over dates --}}
        <div class = 'element table'>
            <caption class = 'key'> bills over dates :</caption>

            <div class="margin-top">
                <table class="table">
                    <tr>
                        @php
                            $array = ['date', 'bill'];
                        @endphp
                        @foreach ($array as $colomn)
                            <th>{{ $colomn }}</th>
                        @endforeach
                    </tr>
                    @foreach ($data['carts chart'] as $category => $value)
                        <tr class="items">
                            <td>
                                {{ $category }}
                            </td>
                            <td>
                                {{ $value . ' S.P' }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        {{-- Carts details --}}
        <div class = 'element table'>
            <caption class = 'key'>Carts details:</caption>

            <div class="margin-top">
                <table class="table">
                    <tr>
                        @php
                            $array = ['id', 'status', 'bill'];
                        @endphp
                        @foreach ($array as $colomn)
                            <th>{{ $colomn }}</th>
                        @endforeach
                    </tr>
                    @foreach ($data['carts'] as $user)
                        <tr class="items">
                            <td>
                                {{ $user['id'] }}
                            </td>
                            <td>
                                {{ $user['payment_status'] }}
                            </td>
                            <td>
                                {{ $user['bill'] . ' S.P' }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

    </div>

    <div class="footer margin-top">
        {{-- <div>Thank you</div> --}}
        <div>&copy; MedHub {{ date('Y') }}</div>
    </div>
</body>

</html>
