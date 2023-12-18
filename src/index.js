// Define the icon for the Text & Media block variation.
import { ReactComponent as Icon } from './icon.svg';

wp.blocks.registerBlockVariation(
	'core/media-text',
	{
		name: 'text-media',
		title: 'Text & Media',
		icon: <Icon />,
		attributes: {
			align: 'wide',
			backgroundColor: 'accent',
			mediaPosition: 'right'
		},
		isActive: [ 'mediaPosition' ]
	}
);

