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
            @if (
                !in_array($key, [
                    'from',
                    'to',
                    'user',
                    'joined users info',
                    'categories percentages for sold medicines',
                    'income chart',
                    'profit chart',
                    'payed orders info',
                ]))
                <div class = 'element'>
                    <div class = 'key'> {{ $key }} :</div>
                    {{ $item }}
                </div>
            @endif
        @endforeach

        {{-- Joined users info --}}
        <div class = 'element table'>
            <caption class = 'key'> Joined users information :</caption>

            <div class="margin-top">
                <table class="table">
                    <tr>
                        @php
                            $array = ['name', 'phoneNumber', 'pharmacyName'];
                        @endphp
                        @foreach ($array as $colomn)
                            <th>{{ $colomn }}</th>
                        @endforeach
                    </tr>
                    @foreach ($data['joined users info'] as $user)
                        <tr class="items">
                            @foreach ($array as $key)
                                <td>
                                    {{ $user[$key] }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        {{-- Paid orders information --}}
        <div class = 'element table'>
            <caption class = 'key'> Paid orders information :</caption>

            <div class="margin-top">
                <table class="table">
                    <tr>
                        @php
                            $array = ['id', 'user', 'phoneNumber', 'bill', 'profit'];
                        @endphp
                        @foreach ($array as $colomn)
                            <th>{{ $colomn }}</th>
                        @endforeach
                    </tr>
                    @foreach ($data['payed orders info'] as $cart)
                        <tr class="items">
                            @foreach ($array as $key)
                                @if ($key == 'user')
                                    <td>
                                        {{ $cart['user']['name'] }}
                                    </td>
                                @elseif($key == 'phoneNumber')
                                    <td>
                                        {{ $cart['user']['phoneNumber'] }}
                                    </td>
                                @else
                                    <td>
                                        {{ $cart[$key] }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        {{-- Categories percentages for sold medicines --}}
        <div class = 'element table'>
            <caption class = 'key'> Categories percentages for sold medicines :</caption>

            <div class="margin-top">
                <table class="table">
                    <tr>
                        @php
                            $array = ['index', 'name', 'precentage'];
                        @endphp
                        @foreach ($array as $colomn)
                            <th>{{ $colomn }}</th>
                        @endforeach
                    </tr>
                    @foreach ($data['categories percentages for sold medicines'] as $category => $value)
                        <tr class="items">
                            <td>
                                {{ $loop->index + 1 }}
                            </td>
                            <td>
                                {{ $category }}
                            </td>
                            <td>
                                {{ $value }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        {{-- income over dates --}}
        <div class = 'element table'>
            <caption class = 'key'> Income over dates :</caption>

            <div class="margin-top">
                <table class="table">
                    <tr>
                        @php
                            $array = ['date', 'income'];
                        @endphp
                        @foreach ($array as $colomn)
                            <th>{{ $colomn }}</th>
                        @endforeach
                    </tr>
                    @foreach ($data['income chart'] as $category => $value)
                        <tr class="items">
                            <td>
                                {{ $category }}
                            </td>
                            <td>
                                {{ $value }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        {{-- profit over dates --}}
        <div class = 'element table'>
            <caption class = 'key'> Profit over dates :</caption>

            <div class="margin-top">
                <table class="table">
                    <tr>
                        @php
                            $array = ['date', 'profit'];
                        @endphp
                        @foreach ($array as $colomn)
                            <th>{{ $colomn }}</th>
                        @endforeach
                    </tr>
                    @foreach ($data['profit chart'] as $category => $value)
                        <tr class="items">
                            <td>
                                {{ $category }}
                            </td>
                            <td>
                                {{ $value }}
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
