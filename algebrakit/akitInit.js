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

        let oldElms = document.querySelectorAll(`akit-exercise[cached-ref="${ref}"]`);
        for (let ii = 0; ii < oldElms.length; ii++) {
            oldElms[ii].setAttribute('session-id', obj.sessionId);
            oldElms[ii].removeAttribute('cached-ref');
            if(oldElms[ii].hasAttribute('show-solution-button')) addSolutionButton(oldElms[ii], obj.sessionId);
        }
        oldElms = document.querySelectorAll(`akit-interaction[cached-ref="${ref}"]`);
        for (let ii = 0; ii < oldElms.length; ii++) {
            oldElms[ii].setAttribute('session-id', obj.sessionId);
            oldElms[ii].removeAttribute('cached-ref');
        }
    })
    delete AlgebraKIT.cachedRefMap;
}

function addSolutionButton(parent, sessionId) {
    let elm = document.createElement('div');
    parent.after(elm);
    elm.innerHTML = '<button class="wp-akit-button">solution</button>';
    elm.addEventListener('click', () => showSolution(elm, sessionId));
}

function showSolution(button, sessionId) {
    button.outerHTML = `<akit-exercise session-id="${sessionId}" solution-mode></akit-exercise>`;
}

loadCachedRefs();