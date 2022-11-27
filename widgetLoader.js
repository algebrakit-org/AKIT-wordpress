function loadJs() {
    var script = document.createElement('script');
    script.src = "${WIDGET_HOST}/akit-widgets.min.js";
    document.body.appendChild(script);
}
if (typeof(AlgebraKIT) == 'undefined') {
    var AlgebraKIT = {
        config: {
            theme: '${THEME}',
            styles: {
                general: {
                    'border-radius': '10px'
                },
                multistep: {
                    'color-worksheet-bg': 'white',
                    'border-worksheet': '1px solid #ccc',
                    'color-buttons-bg': 'white',
                    'color-buttons-bg-hover': '#ccc',
                }
            }
        }
    };
    loadJs();
}
else if (!AlgebraKIT._api) {
    loadJs();
}