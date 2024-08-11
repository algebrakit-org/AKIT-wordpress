/** 
 * To allow efficient creation of multiple exercises in a page, the WP plugin (which is PHP code, so backend) collects all exercise 
 * references and calls /session/create only once for the whole batch. The response of /session/create is stored as a JS definition of 
 * AlgebraKIT.cachedRefMap. The map uses a unique ID as key ('cached-ref')
 * Here (frontend), we push that information to the AlgebraKIT global, so that all exercises can initialize without a
 * roundtrip to the server.
 * Next, replace the cached-ref attribute to the session-id attribute of each akit-exercise or akit-interaction instance. This 
 * will trigger initialization of the akit-exercise or akit-interaction.
 * */
function loadCachedRefs() {
    if (!AlgebraKIT.cachedRefMap) {
        return;
    }
    Object.keys(AlgebraKIT.cachedRefMap).forEach(ref => {
        let obj = AlgebraKIT.cachedRefMap[ref];
        AlgebraKIT.addSessionData(obj.sessionId, 0, obj.data);

        let oldEx = document.querySelectorAll(`akit-exercise[cached-ref="${ref}"]`);
        for (let ii = 0; ii < oldEx.length; ii++) {
            oldEx[ii].setAttribute('session-id', obj.sessionId);
            oldEx[ii].removeAttribute('cached-ref');
            if(oldEx[ii].hasAttribute('show-solution-button')) addSolutionButton(oldEx[ii], obj.sessionId);
            if(oldEx[ii].hasAttribute('show-repeat-button')) addRepeatButton(oldEx[ii], obj.sessionId);
            if(oldEx[ii].hasAttribute('show-edit-button')) addEditButton(oldEx[ii], obj.sessionId);
            if(oldEx[ii].hasAttribute('handwriting')) {
                let behavior = {
                    general: {
                        handwriting: {
                            mode: oldEx[ii].getAttribute('handwriting')
                        }
                    }
                };
                akitWithOptions(oldEx[ii], behavior);
            }
        }
        let oldInter = document.querySelectorAll(`akit-interaction[cached-ref="${ref}"]`);
        for (let ii = 0; ii < oldInter.length; ii++) {
            oldInter[ii].removeAttribute('cached-ref');
            oldInter[ii].setAttribute('session-id', obj.sessionId);
            if(oldInter[ii].hasAttribute('handwriting')) {
                let behavior = {
                    general: {
                        handwriting: {
                            mode: oldInter[ii].getAttribute('handwriting')
                        }
                    }
                };
                akitWithOptions(oldInter[ii], behavior);
            }
        }
    })
    delete AlgebraKIT.cachedRefMap;
}

async function akitWithOptions(elm, behavior) {
    await AlgebraKIT.initializedPromise;

    let iframe = document.createElement('iframe');
    iframe.setAttribute('width', '100%');
    iframe.style.border = 'none';
    let akitObj = JSON.parse(JSON.stringify(AlgebraKIT));
    if(!akitObj.config) akitObj.config = {};
    akitObj.config.behavior = akitObj.config.behavior
        ?deepMerge(akitObj.config.behavior, behavior)
        :behavior;
    let configStr = JSON.stringify(akitObj);
    // creating <script> tags when interpolating strings seems to be problematic, so
    // we'll replace _script with script afterwards
    let widgetsUrl = AlgebraKIT._api.widgetHost.indexOf('localhost')>=0
            ? AlgebraKIT._api.widgetHost+'/akit-widgets.js'
            : AlgebraKIT._api.widgetHost+'/akit-widgets.min.js';
    let html = elm.outerHTML + `
        <_script>
            AlgebraKIT = ${configStr};
        </_script>
        <_script src="${widgetsUrl}"></_script>
    `;
    elm.parentElement.replaceChild(iframe, elm);
    iframe.contentWindow.document.open();
    iframe.contentWindow.document.write(html.replace(/_script/g, 'script'));
    iframe.contentWindow.document.close();

    // Resize the iframe when its content changes
    const iframeBody = iframe.contentDocument.body;
    const ro = new ResizeObserver(function() {
        // Called when the body size changes
        iframe.height = iframe.contentWindow.document.body.scrollHeight;
      });
      
      ro.observe(iframeBody);
      
}

function addSolutionButton(parent, sessionId) {
    let elm = document.createElement('div');
    parent.after(elm);
    elm.innerHTML = '<button class="wp-akit-button">solution</button>';
    elm.addEventListener('click', () => showSolution(elm, sessionId));
}

function addRepeatButton(parent, sessionId) {
    let elm = document.createElement('div');
    parent.after(elm);
    elm.innerHTML = '<button class="wp-akit-button">repeat</button>';
    elm.addEventListener('click', () => repeat(elm, sessionId));
}
function addEditButton(parent, sessionId) {
    let elm = document.createElement('div');
    parent.after(elm);
    elm.innerHTML = '<button class="wp-akit-button">edit</button>';
    elm.addEventListener('click', () => edit(elm, sessionId));
}

function showSolution(button, sessionId) {
    button.outerHTML = `<akit-exercise session-id="${sessionId}" solution-mode></akit-exercise>`;
}

function repeat(button, sessionId) {
    // to do
}

function edit(button, sessionId) {
    // to do
}

function deepMerge(obj1, obj2) {
    for (let key in obj2) {
      if (obj2.hasOwnProperty(key)) {
        if (obj2[key] instanceof Object && obj1[key] instanceof Object) {
          obj1[key] = deepMerge(obj1[key], obj2[key]);
        } else {
          obj1[key] = obj2[key];
        }
      }
    }
    return obj1;
  }
  
  
loadCachedRefs();