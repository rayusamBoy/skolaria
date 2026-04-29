<table width="100%" class="mb-3">
    <tr>
        <td width="100">
            <img src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('type', 'logo')) }}" style="max-height: 80px;">
        </td>
        <td class="text-center">
            <div style="font-size: 22px; font-weight: bold; color: #1b0c80;">
                {{ strtoupper($settings->firstWhere('type', 'system_name')->description) }}
            </div>
            <div>{{ ucwords($settings->firstWhere('type', 'address')->description) }}</div>
            <div class="mt-2" style="font-size: 16px;">
                <strong>
                    RESULTS SHEET: {{ strtoupper($my_class->name) }}
                    @if(isset($section)) {{ strtoupper($section->name) }} @endif
                    - {{ strtoupper($ex->name) }} ({{ $year }})
                </strong>
            </div>
        </td>
    </tr>
</table>
