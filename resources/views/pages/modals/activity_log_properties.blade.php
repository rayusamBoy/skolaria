<!-- Modal -->
@php
    $attributes = $chk->properties['attributes'] ?? [];
    $old = $chk->properties['old'] ?? [];
    $keys = array_keys($attributes)
@endphp
<div
    class="modal fade"
    id="activity-log-properties-{{ $chk->id }}"
    tabindex="-1"
    aria-labelled="Activity Log Properties"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">Close</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table-bordered table-responsive table">
                    <tr>
                        <td>Key</td>
                        <td>Attribute</td>
                        <td>Old</td>
                    </tr>
                    @foreach ($keys as $key)
                        @php
                            $new_value = array_key_exists($key, $attributes) ? (is_array($attributes[$key]) ? implode(',', $attributes[$key]) : $attributes[$key]) : 'N/A';
                            $old_value = array_key_exists($key, $old) ? (is_array($old[$key]) ? implode(',', $old[$key]) : $old[$key]) : 'N/A';
                        @endphp
                        <tr>
                            <td>{{ $key }}</td>
                            <td class="break-spaces break-all">{{ $new_value }}</td>
                            <td class="break-spaces break-all">{{ $old_value }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
