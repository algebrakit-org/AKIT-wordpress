function loadJs() {
    var script = document.createElement('script');
    script.src = "${WIDGET_HOST}/akit-widgets.min.js";
    document.body.appendChild(script);
}
if (typeof(AlgebraKIT) == 'undefined') {
    var AlgebraKIT = {
        config: {
            theme: '${THEME}'
        }
    };
    loadJs();
}
else if (!AlgebraKIT._api) {
    loadJs();
}