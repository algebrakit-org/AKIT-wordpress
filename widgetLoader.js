const REST_ENDPOINT = '/akit-site/wp-json';
const ENDPOINT_SESSION_CREATE = REST_ENDPOINT + '/akit/v1/session/create';

/**
 * Load all scripts
 */
function loadJs() {
    // akit frontend 
    var script = document.createElement('script');
    script.src = "${WIDGET_HOST}/akit-widgets.min.js";
    document.body.appendChild(script);
    
    // font awesome icons
    script = document.createElement('script');
    script.src = 'https://kit.fontawesome.com/6b2e20adcb.js';
    document.body.appendChild(script);
    
}

function postJSON(url, data) {
    return new Promise( (resolve, reject) => {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var json = JSON.parse(xhr.responseText);
                resolve(json);
            } else {
                reject('call failed');
            }
        };
        xhr.send(JSON.stringify(data));    
    });
}

async function handleRepeatButton(cachedRef, exerciseId) {
    let session = postJSON(ENDPOINT_SESSION_CREATE, {exercises:[{exerciseId:exerciseId}]});
    alert('Repeat button clicked');
}

/**
 * Create the proper html for each akit-wp-exercise
 */
function setAkitWPExercises() {

    let exArr = document.getElementsByTagName('akit-wp-exercise');
    for(let ii=0; ii<exArr.length; ii++) {
        let wpEx = exArr[ii];
        let ex = document.createElement('akit-exercise');
        let repeatButton = false;
        let editButton = false;
        let showButtonBar = false;
        let cachedRef = null;
        let exerciseId = null;

        // copy attributes to akit-exercise. Handle special attributes (repeat-button)
        for(let aa = 0; aa<wpEx.attributes.length; aa++) {
            let attr = wpEx.attributes[aa];
            switch(attr.name) {
                case 'repeat-button': repeatButton = true; showButtonBar = true; break;
                case 'edit-button':   editButton = true; showButtonBar = true; break;
                case 'cached-ref': cachedRef = attr.value;  //no break
                case 'exercise-id': exerciseId = attr.value; //no break
                default: ex.setAttribute(attr.name, attr.value);
            }
        }

        if(showButtonBar) {
            let barElm = document.createElement('div');
            barElm.className = 'wp-akit-buttonbar'
            wpEx.appendChild(barElm);

            if(repeatButton){
                let rbElm = document.createElement('div');
                rbElm.className = 'wp-akit-repeat wp-akit-button';
                rbElm.innerHTML = '<i class="fa-solid fa-rotate-right"></i>';
                rbElm.addEventListener('click', ()=>handleRepeatButton(cachedRef, exerciseId))
                barElm.appendChild(rbElm);
            }
            if(editButton){
                let editElm = document.createElement('div');
                editElm.className = 'wp-akit-repeat wp-akit-button';
                editElm.innerHTML = '<i class="fa-regular fa-pen-to-square"></i>';
                editElm.addEventListener('click', handleRepeatButton)
                barElm.appendChild(editElm);
            }
        }
        wpEx.appendChild(ex);
    }
}

/**
 * MAIN
 */
if (typeof(AlgebraKIT) == 'undefined') {
    var AlgebraKIT = {
        config: {
            theme: '${THEME}',
            styles: {
                general: {
                    'border-radius-buttons': '1em',
                    'shadow-buttons': 'none',
                    'padding-buttons': '0.6em 1.5em',
                    'height-form-controls': '40px',
                    'border-radius-form-controls': '10px'
                },
                'formula-editor': {
                    'border-radius-bottom': '10px',
                },
                multistep: {
                    'color-worksheet-bg': 'white',
                    'border-worksheet': '1px solid #ccc',
                    'border-radius': '10px',
                    'border-radius-buttons': '1em',
                    'color-buttons-fg': 'var(--akit-color-primary)',
                    'color-buttons-fg-hover': 'white',
                    'color-buttons-bg': 'white',
                    'color-buttons-bg-hover': 'var(--akit-color-primary)',
                    'border-buttons': '1px solid var(--akit-color-primary)',
                    'border-buttons-hover': 'none',
                    'border-radius-buttons': '1em',
        
                }
            }
        }
    };
    loadJs();
}
else if (!AlgebraKIT._api) {
    loadJs();
}
setAkitWPExercises();