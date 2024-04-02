<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Account Fee</title>
    <link href="{{ storage_path('fonts/noto-sans-cjk-jp/css/font.css')}}" rel="stylesheet">
    <style type="text/css">
        #invoice-details tbody tr:nth-child(even) {
            background-color: #F5F8F9;
        }

        .th-custom,
        .td-custom {
            height: 50px;
            padding-left: 10px;
            font-size: 14px;
        }

        .td-custom {
            line-height: 20px;
            color: #788b9a;
        }

        .va-table--striped tr:nth-child(even) td {
            background-color: #ecf0f1;
        }

        body {
            font-family: "Noto Sans Japanese", Helvetica, Arial, sans-serif;
        }

        .page {
            width: 100%;
        }

        #invoice-details {
            width: 100%;
        }

        .th-custom {
            text-align: left;
        }

        .td-custom {
            text-align: left;
        }
    </style>

</head>

<body>
    @php
    use Carbon\Carbon;
    @endphp
    <table width="100%" style="padding-bottom: 28px; border-bottom: 10px solid #1C78DD;">
        <tr>
            <td style="color: #1C78DD; text-transform: uppercase; font-weight: 700;">
                <div class="my-japanese-text" style="font-size: 40px;line-height: 46px">明細書</div>
            </td>
            <td style="text-align: right; color: #1C78DD; font-weight: 400; font-size: 14px; line-height: 20px;">
                <div class="my-japanese-text" style="font-size: 32px;line-height:
                46px">{{$contractorIsHonsha->company_name}}</div>
                <div style="max-width: 305px ;display: inline-block;">
                    {{$contractorIsHonsha->address}} <br />
                    {{$contractorIsHonsha->email}} <br />
                    {{$contractorIsHonsha->manager}}
                </div>
            </td>
        </tr>
    </table>
    <table style="padding-top: 15px; width:100%; table-layout: auto; width: 100%">
        <tr>
            <td><span style="color: #1C78DD; text-transform: uppercase; font-weight: 700; font-size: 22px; line-height: 32px;">Bill to</span></td>
            <td></td>
        </tr>
    </table>
    <div style="border-bottom: 3px solid #1C78DD; margin-bottom: 30px">
        <div style="display: inline-block; padding-bottom: 50px;">
            <table style="clear: both">
                <tr width="100%">
                    <td style="width: 50%">
                        <span style="font-weight: 700;font-size: 18px;color: #06152b;">{{$contructor->company_name}}</span>
                    </td>
                </tr>
                <tr>
                    <td style="margin: 0;padding: 0;border: 0;font-size: 100%;vertical-align: baseline;box-sizing: border-box;">
                        <span>{{$contructor->address}}</span>
                    </td>
                </tr>
                <tr>
                    <td style="margin: 0;padding: 0;border: 0;font-size: 100%;vertical-align: baseline;box-sizing: border-box;"> <span>{{$contructor->email}}</span></td>
                </tr>
            </table>
        </div>
        <div style="float: right">
            <table style="clear: both">
                <tr width="100%">
                    <td style="width: 50%">
                        <span style="font-weight: 700;font-size: 18px;color: #06152b;">Date:</span><br>
                        <span>{{ Carbon::parse($invoiceContructor['invoice_date'])->format('Y.m.d') }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%">
                        <span style="font-weight: 700;font-size: 18px;color: #06152b;">Number:</span><br>
                        <span>{{$invoiceContructor['number'] ?? ''}}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="page">
        <table width="100%" id="invoice-details" cellspacing="0" cellpadding="0" class="va-table--striped">
            <thead style="background-color: #1C78DD; color: white; font-weight: 700; line-height: 20px;">
                <tr>
                    <th align="left" class="th-custom">DESCRIPTION</th>
                    <th align="left" class="th-custom">BANK NAME</th>
                    <th align="left" class="th-custom">ACCOUNT NUMBER</th>
                    <th align="left" class="th-custom">QTY</th>
                    <th align="left" class="th-custom">AMOUNT</th>
                    <th align="left" class="th-custom">RATE</th>
                    <th align="left" class="th-custom">COMMISION</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($depositListWithdraw as $key => $val)
                @foreach ($val as $val2)
                @foreach ($val2 as $val3)
                @php $commission=$val3->amount * $val3->commission_rate / 100;
                @endphp
                <tr>
                    <td align="right" class="td-custom">{{trans('invoice.'.$key, [], $lang)}}</td>
                    <td align="right" class="td-custom">{{$val3->bank_name}}</td>
                    <td align="right" class="td-custom">{{$val3->account_number}}</td>
                    <td align="right" class="td-custom">{{$val3->number_trans}}</td>
                    <td align="right" class="td-custom">¥{{fmod($val3->amount, 1) == 0 ? number_format(intval($val3->amount)) : number_format($val3->amount)}}</td>
                    <td align="right" class="td-custom">{{fmod($val3->commission_rate, 1) == 0 ? intval($val3->commission_rate) : $val3->commission_rate}}%</td>
                    <td align="right" class="td-custom">¥{{fmod($commission, 1) == 0 ? number_format(intval($commission)) : number_format($commission)}}</td>
                </tr>
                @endforeach
                @endforeach
                @endforeach

            </tbody>
        </table>
    </div>
    <table style="padding-top: 16px; width: 100%">
        @php
        $total = 0;
        @endphp
        <tr>
            <td align="right">
                <span align="right" style="color: #1C78DD;font-weight: 700;font-size: 16px;line-height: 23px; text-align: right" colspan="4">Total:</span>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span align="right" style="color: #1C78DD;font-weight: 700;font-size: 26px;line-height: 38px;">¥
                    @foreach ($depositListWithdraw as $key => $val)
                    @foreach ($val as $val2)
                    @foreach ($val2 as $val3)
                    @php $total += $val3->amount * $val3->commission_rate / 100 @endphp
                    @endforeach
                    @endforeach
                    @endforeach
                    {{fmod($total, 1) == 0 ? number_format(intval($total)) : number_format($total)}}
                </span>
            </td>
        </tr>
    </table>
    <div>
        <table>
            <tr>
                <td style="font-weight: 550; font-size: 16px; line-height: 23px">Thank you for your
                    business!</td>
            </tr>
        </table>
        <table style="line-height: 23px; font-size: 16px;">
            <tr>
                <td>
                    <div style="color: #06152B; font-weight: 550; width: 510px; overflow-wrap: anywhere;">Note:
                        {{$invoiceContructor['note'] ?? ''}}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
