<script type="module">
    /**
     *-------------------------------------------------------------
     * Authentication and element caching
     *-------------------------------------------------------------
     */
    const authId = {{ auth()->id() }};
    const authUserRoles = JSON.parse('@json(auth()->user()->roles->pluck('name'))');
    const superAdminRoleName = "{{ config('roles.default_roles.tenant.superadmin') }}";
    const $textarea = $('#message');
    const $sendBtn = $('#send-message-btn');
    const $typingStatus = $('#user-is-typing');
    const $typingIndicator = $('#typing-indicator');
    const $messagesRow = $('#messages-row');
    const $msgTxtAreaHint = $('#message-textarea-actions-subtle-hint');

    let typingNow = 0,
        typingTimeout;

    // Open a private channel using Echo (server-side values injected)
    const channel = Echo.private(`messages.{{ $thread->id }}.{{ tenant()?->getTenantKey() }}`);

    /**
     *-------------------------------------------------------------
     * Trigger a typing event via whisper
     *-------------------------------------------------------------
     */
    const isTyping = (status) => {
        channel.whisper('typing', {
            user_name: "{{ auth()->user()->name }}",
            typing: status,
        });
    };

    /**
     *-------------------------------------------------------------
     * On keydown in the message textarea, trigger typing indicator
     *-------------------------------------------------------------
     */
    $textarea.on('keydown', (e) => {
        if (typingNow < 1) {
            isTyping(true);
            typingNow = 1;
        }

        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            isTyping(false);
            typingNow = 0;
            $msgTxtAreaHint.show(250);
        }, 1000);

        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $sendBtn.click();
        }
    });

    /**
     *-------------------------------------------------------------
     * Bind connection state change and check internet connectivity
     *-------------------------------------------------------------
     */
    Echo.connector.pusher.connection.bind('state_change', (states) => {
        checkInternet(states.current);
    });

    /**
     *-------------------------------------------------------------
     * Listen for typing whispers from other users
     *-------------------------------------------------------------
     */
    channel.listenForWhisper('typing', (e) => {
        if (e.typing) {
            $typingStatus.html(`${e.user_name} is typing`);
            $typingIndicator.css('display', 'inline-block');
        } else {
            $typingIndicator.hide();
        }
    });

    /**
     *-------------------------------------------------------------
     * Listen for new messages and update UI accordingly
     *-------------------------------------------------------------
     */
    channel.listen('MessageArrived', (e) => {
        $messagesRow.append(e.message_html);
        updateParticipantLastRead(e.message.thread_id);
        playNotificationSound('message-notification.mp3', true);
        initializePopover(); // Re-initialize popover for new elements
        scrollTo($sendBtn); // Scroll to bottom (or to button)
    });

    /**
     *-------------------------------------------------------------
     * Listen for message deletion events
     *-------------------------------------------------------------
     */
    channel.listen('MessageDeleted', (e) => {
        $('#confirm-message-delete').find('.close').click(); // Close modal if open
        $(`#message-${e.message.id}`).replaceWith(e.message_html);
    });
</script>
<script type="text/javascript">
    /**
     *-------------------------------------------------------------
     * Cache frequently used jQuery selectors
     *-------------------------------------------------------------
     */
    const $loadPrevBtn = $('button.load-previous');
    const $loadingPrevDiv = $('div.loading-previous');
    const $messagesRow = $('#messages-row');

    /**
     *-------------------------------------------------------------
     * Prepare deletion form attributes dynamically
     *-------------------------------------------------------------
     */
    function prepareDeleteUserMessageForm(messageId) {
        hideAllPopover();
        const actionUrl = '{{ route("threads.messages.user_delete", ":message_id") }}'.replace(':message_id', messageId);
        $('form#delete-user-message').attr('action', actionUrl);
        popOutIntendedMesg(messageId);
    }

    function popOutIntendedMesg(messageId) {
        $('div#messages-row').find('div.right, div.left').css('z-index', 'initial');
        $(`#message-${messageId}`).find('div.right, div.left').css('z-index', '99999');
    }

    /**
     *-------------------------------------------------------------
     * Handle "Load Previous Messages" button click
     *-------------------------------------------------------------
     */
    $loadPrevBtn.on('click', (e) => {
        e.preventDefault();
        const currentFirstMsgId = $messagesRow.children(':first').attr('id').split('-')[1];
        let url = '{{ route("threads.messages.previous", [":thread_id", ":current_first_msg_id_in_view"]) }}'
            .replace(':thread_id', '{{ $thread->id }}')
            .replace(':current_first_msg_id_in_view', currentFirstMsgId);
        const requestData = {
            url,
            prepend_previous_messages: true,
            show_loading_previous: true,
        };
        processRequest(requestData);
    });

    /**
     *-------------------------------------------------------------
     * Update participant's last read timestamp (if document is visible)
     *-------------------------------------------------------------
     */
    function updateParticipantLastRead(threadId) {
        if (document.hidden) return;
        let url = '{{ route("threads.participants.update.last_read", [":thread_id"]) }}'.replace(':thread_id', threadId);
        processRequest({
            url: url,
        });
    }

    /**
     *-------------------------------------------------------------
     * Process an AJAX request with optional UI feedback
     *-------------------------------------------------------------
     */
    async function processRequest(data) {
        const formData = new FormData();

        if (data.show_loading_previous) {
            $loadPrevBtn.hide();
            $loadingPrevDiv.fadeIn();
        }

        $.ajax({
            url: data.url,
            type: 'POST',
            cache: false,
            processData: false,
            dataType: 'json',
            contentType: false,
            data: formData,
            global: false,
        })
            .done((resp) => {
                if (data.prepend_previous_messages) {
                    if (!resp.msg) {
                        $loadingPrevDiv.hide();
                        $loadPrevBtn.hide('slow');
                    } else {
                        $messagesRow.prepend(resp.msg).fadeIn('slow');
                        initializePopover();
                        if (data.show_loading_previous) {
                            $loadingPrevDiv.hide();
                            $loadPrevBtn.fadeIn();
                        }
                    }
                }
                return resp;
            })
            .fail((e) => {
                pop({
                    msg: 'Sorry, something went wrong. You may try again!',
                    type: 'error',
                });
                if (data.show_loading_previous) {
                    $loadingPrevDiv.hide();
                    $loadPrevBtn.fadeIn();
                }
            });
    }

    /**
     *-------------------------------------------------------------
     * Check internet connection based on pusher state
     *-------------------------------------------------------------
     */
    function checkInternet(state) {
        const $msgRow = $('.message-row');
        const $alertRow = $('.alert-row');
        const dotIndicatorClass = getRandomDotIndicatorClass();

        if (state === 'connected') {
            $alertRow.hide();
            $msgRow.show();
        } else if (state === 'connecting') {
            $msgRow.hide();
            $alertRow.find('.alert').removeClass('text-center');
            $alertRow
                .find('.dot-indicator')
                .removeClass('display-none')
                .css({
                    top: '45%',
                    right: '2rem',
                    position: 'absolute',
                })
                .addClass(dotIndicatorClass);
            $alertRow.show().find('i').text('Connecting');
        } else {
            $msgRow.hide();
            $alertRow.find('.alert').addClass('text-center');
            $alertRow.find('.dot-indicator').addClass('display-none').removeClass(dotIndicatorClass);
            $alertRow.show().find('i').text('No connection');
        }
    }

    /**
     *-------------------------------------------------------------
     * Return a random dot indicator class from an array
     *-------------------------------------------------------------
     */
    function getRandomDotIndicatorClass() {
        const dotClasses = ['dot-flashing', 'dot-elastic', 'dot-collision'];
        return dotClasses[Math.floor(Math.random() * dotClasses.length)];
    }
</script>
