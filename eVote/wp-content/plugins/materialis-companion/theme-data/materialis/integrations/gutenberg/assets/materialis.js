var registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};

registerBlockType( 'extendstudio/materialis', {
    title: 'Materialis Block',

    icon: 'html',

    category: 'layout',

    supports: {
		 customClassName: false,
		 className: false,
         html: false,
         inserter: false,
         reusable: false,
    },
    
    attributes: {
		content: {
			type: 'string',
			source: 'html',
		},
	},
    
    edit: function(props) {
        var content = props.attributes.content;
        return wp.element.createElement(wp.element.RawHTML, null, content);;
    },

    save: function( props  ) {
        var content = props.attributes.content;
        return wp.element.createElement(wp.element.RawHTML, null, content);
    },
} );