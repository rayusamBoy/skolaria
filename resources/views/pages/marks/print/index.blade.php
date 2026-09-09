<html>
<head>
    <title>Student Marksheet - {{ $sr->user->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/my_print.css') }}" />

    @laravelPWA
</head>

<body>
    <div class="container">
        <div id="print" xmlns:margin-top="http://www.w3.org/1999/xhtml">
            {{-- Logo and School Details--}}
            <table width="100%">
                <tr>
                    <td>
                        <img
                            src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key', 'logo')) }}"
                            style="max-height: 100px"
                        />
                    </td>

                    <td style="text-align: center">
                        <strong
                            ><span
                                style="color: #1b0c80; font-size: 25px"
                                >{{ strtoupper($settings->firstWhere('key', 'system_name')->value) }}</span
                            ></strong
                        ><br />
                        <strong
                            ><span style="color: #000; font-size: 15px"
                                ><i>{{ ucwords($settings->firstWhere('key', 'address')->value) }}</i></span
                            ></strong
                        ><br />
                        <strong
                            ><span style="color: #000; font-size: 15px"
                                >RESULT SHEET {{ '(' . strtoupper($class_type->name) . ')' }}</span
                            ></strong>
                    </td>
                    <td style="width: 100px; height: 100px; float: left">
                        <img
                            src="{{ Usr::getTenancyAwarePhotoUrl($sr->user->photo_path) }}"
                            alt="..."
                            width="100"
                            height="100"
                        />
                    </td>
                </tr>
            </table>
            <br />

            {{-- Background Logo --}}
            <div class="bg-div">
                <img src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key', 'logo')) }}" />
            </div>

            {{-- Marksheet Print --}}
            @include('pages.marks.print.sheet')
            {{-- Key to Grading --}}
            @include('pages.marks.print.grading')
            {{-- Key to Divisioning --}}
            @include('pages.marks.print.divisioning')
            {{-- TRAITS - PSCHOMOTOR & AFFECTIVE --}}
            @include('pages.marks.print.skills')

            <div style="margin-top: 25px; clear: both"></div>

            {{-- COMMENTS & SIGNATURE    --}}
            @include('pages.marks.print.comments')
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
