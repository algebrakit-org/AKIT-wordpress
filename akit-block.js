(function (blocks, element) {
    var el = element.createElement;

    blocks.registerBlockType('algebrakit/algebrakit-exercise', {
        title: 'AlgebraKiT Exercise',
        icon: 'universal-access-alt',
        category: 'widgets',
        example: {},
        edit: function (props) {
            var exerciseId = props.attributes.exerciseId;
            var majorVersion = props.attributes.majorVersion;

            function onChangeExerciseId(event) {
                props.setAttributes({ exerciseId: event.target.value });
            }

            function onChangemajorVersion(event) {
                props.setAttributes({ majorVersion: event.target.value });
            }

            return el('form', { className: props.className },
                el('strong', null, 'AlgebraKiT Exercise settings'),
                el('br'),
                el('table', null, 
                    el('tr', null, 
                        el('td', null, el('label', null, 'Exercise ID ')),
                        el('td', null, el('input', { id: 'exerciseId', name: 'exerciseId', 'type': 'text', 'placeholder': 'Exercise ID', onChange: onChangeExerciseId, value: exerciseId })),
                    ),
                    el('tr', null, 
                        el('td', null, el('label', null, 'Major Version ')),
                        el('td', null, el('input', { id: 'majorVersion', name: 'majorVersion', 'type': 'text', 'placeholder': 'Major Version', onChange: onChangemajorVersion, value: majorVersion })),
                    )
                )
            )
        },
        save ( props ) {
            return null // See PHP side. This block is rendered on PHP.
        },
    });
}(
    window.wp.blocks,
    window.wp.element
));