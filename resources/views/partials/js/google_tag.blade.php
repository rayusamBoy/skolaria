<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings->firstWhere("type", "google_analytic_tag_id")->value }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '{{ $settings->firstWhere("type", "google_analytic_tag_id")->value }}');

</script>