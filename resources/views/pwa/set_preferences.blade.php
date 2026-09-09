<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Setting preferences</title>
</head>
<body>
    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);
            const theme = params.get('theme');
            const skin = params.get('skin');
            let url = params.get('url');

            if (theme) localStorage.setItem('theme', theme);
            if (skin) localStorage.setItem('skin', skin);

            if (url) {
                const allowedBase = window.location.origin;
                try {
                    const parsed = new URL(url, allowedBase);
                    if (parsed.origin !== allowedBase) {
                        document.body.textContent = 'Invalid redirect URL.';
                        return;
                    }
                } catch (e) {
                    document.body.textContent = 'Invalid URL format.';
                    return;
                }

                setTimeout(function () {
                    window.location.href = url;
                }, 200);
            }
        })();
    </script>
</body>
</html>
