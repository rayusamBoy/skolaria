<table width="100%" class="mb-3">
    <tr>
        @if(config('tenancy.active_identification_mode') === 'request_data')
        <td class="text-center">
            <div style="font-size: 28px; font-weight: bold; color: #0c801b;">
                SMZ
            </div>
            <div style="font-size: 26px; font-weight: bold; color: #0c801b;">
                CENTRAL DISTRICT EXAMINATION BOARD
            </div>
            <div style="font-size: 22px; font-weight: bold; color: #1b0c80;">
                {{ strtoupper($settings->firstWhere('key', 'system_name')->value) }}
            </div>
            <div class="mt-2" style="font-size: 16px;">
                <strong>
                    RESULTS SHEET: {{ strtoupper($my_class->name) }} - {{ strtoupper($ex->name) }} ({{ $year }})
                </strong>
            </div>
        </td>
        @else
        <td width="100">
            <img src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key', 'logo')) }}" style="max-height: 80px;">
        </td>
        @endif
    </tr>
</table>
