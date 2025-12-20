/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useState } from "react";
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

import {
	Disabled,
	TextControl,
	PanelBody,
	PanelRow,
	SelectControl  
} from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';
import metadata from './block.json';
/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes , setAttributes }) {
	
	
	let userid = attributes.userid;
	
	const blockProps = useBlockProps( {
		className: 'ldn-wp_exams ldn-wp_exams-product-rating'
	} );
	return (
		<>
			<InspectorControls>
				
			<PanelBody>
				<PanelRow>
					<TextControl
							label={__("User ID", "wp_exams")}
							value={userid}
							onChange={userid => setAttributes({ userid })}
						/>
				</PanelRow>
				<PanelRow>
					{__("(Optional) User id. Default current logged in user's id.", "wp_exams")}
				</PanelRow>
			</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<Disabled>
					<ServerSideRender
						block={ metadata.name }
						skipBlockSupportAttributes
						attributes={ attributes }
					/>
				</Disabled>
				
			</div>
		</>
	);
}