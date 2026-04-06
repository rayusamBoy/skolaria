<script>
    /**
     *-------------------------------------------------------------
     * Global pop timer for all pop notifications
     *-------------------------------------------------------------
     */
    const pop_timer = {{ session('pop_timer') ?? 20000 }};

    /**
     *-------------------------------------------------------------
     * Login and related pages texts and background preview
     *-------------------------------------------------------------
     */
    @if (session()->has('show_login_and_related_pgs_preview'))
    $('#auth-pages-preview').modal('show');
    @endif

    /**
     *-------------------------------------------------------------
     * Expose all permission names to JS
     * ------------------------------------------------------------
     */
    window.ALL_PERMISSIONS = @if(isset($permissions)) @json($permissions->pluck('name')) @else [] @endif;

    /**
     *-------------------------------------------------------------
     * Pop and flash messages from session
     *-------------------------------------------------------------
     */
    @foreach (['pop_error', 'pop_warning', 'pop_success'] as $type){
      @if (session($type))
        pop({ msg: '{{ session($type) }}', type: '{{ str_replace("pop_", "", $type) }}' @if($type == 'pop_success'), title: 'GREAT!' @endif });
      @endif
    }
    @endforeach

    @foreach (['pop_error_confirm', 'pop_warning_confirm', 'pop_success_confirm'] as $type)
      @if (session($type))
        popConfirm({ msg: '{{ session($type) }}', type: '{{ str_replace("pop_", "", str_replace("_confirm", "", $type)) }}' @if(str_contains($type, 'success')), title: 'GREAT!' @endif });
      @endif
    @endforeach

    @foreach (['flash_info', 'flash_success', 'flash_warning', 'flash_error', 'flash_danger'] as $type)
      @if (session($type))
        flash({ msg: '{{ session($type) }}', type: '{{ str_replace("flash_", "", str_replace("flash_danger", "error", $type)) }}' });
      @endif
    @endforeach
    
    /**
     *-------------------------------------------------------------
     * Global URLs for AJAX calls
     *-------------------------------------------------------------
     */
    const set_notice_as_viewed_url = '{{ route("notices.mark_as_viewed") }}';
    const firebase_url = "{{ route('notifications.firebase.update_device_token') }}";
    const event_create_url = "{{ route('schedule.events.create') }}";
    const update_hidden_alerts_url = "{{ route('my_account.update_hidden_alerts') }}";
    const clear_hidden_alerts_url = "{{ route('my_account.clear_hidden_alerts') }}";
    const get_table_cols_url = '{{ route("ajax.get_table_columns", [":name"]) }}';
    const get_state_url = '{{ route("ajax.get_state", [":id"]) }}';
    const get_lga_url = '{{ route("ajax.get_lga", [":id"]) }}';
    const get_pre_defined_subjects_url = "{{ route('ajax.get_pre_defined_subjects') }}";
    const get_class_students_url = '{{ route("ajax.get_class_students", [":id"]) }}';
    const get_class_sections_url = "{{ route('ajax.get_class_sections', [':id']) }}";
    const get_teacher_class_sections_url = "{{ route('ajax.get_teacher_class_sections', [':id']) }}";
    const get_subject_section_teacher_url = "{{ route('ajax.get_subject_section_teacher', [':sub_id', ':sec_id']) }}";
    const get_year_exams_url = "{{ route('ajax.get_year_exams', [':year']) }}";
    const get_class_subjects_url = "{{ route('ajax.get_class_subjects', [':id']) }}";
    const update_exam_lock_state_url = "{{ route('exams.update_lock_state') }}";
    const update_exam_edit_state_url = "{{ route('exams.update_edit_state') }}";
    const toggle_staff_data_editing_url = "{{ route('users.toggle_staff_data_editing') }}";
    const toggle_user_block_url = "{{ Usr::tenancyInitilized() ? route('users.toggle_user_block') : route('central_users.toggle_user_block') }}";

</script>