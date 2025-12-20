/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
const _ = require('lodash');

import { registerBlockType } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import save from './save';
import Edit from './edit';
import metadata from './block.json';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	icon: {
		src: 
		<svg width="24" height="24" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
			<g>
				<path d="M1.6 43.6h10v16.3h-10V43.6z"/>
			</g>
			<g>
				<path d="M17.1 59.9V41.3h10.1v18.6H17.1z"/>
			</g>
			<g>
				<path d="M32.6 35.8H43v24.1H32.6V35.8z"/>
			</g>
			<g>
				<path d="M48.5 26.2h9.8v33.6h-9.8V26.2z"/>
			</g>
			<g>
				<path d="M1.6 38c0 0 26.5-3.6 44.7-27.6l-5.9-3.9 17.9-6.3V19l-4.9-4.2C53.4 14.8 31.2 39.9 1.6 38z"/>
			</g>
		</svg>
	},
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
	save,
} );
