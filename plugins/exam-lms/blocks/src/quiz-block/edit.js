/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import {
	Disabled, Button,
	TextControl,
	PanelBody,
	PanelRow
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
	
	let quiz_id = attributes.quiz_id;

	const blockProps = useBlockProps( {
		className: 'ldn-wp_exams ldn-wp_exams-checkout'
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wp_exams' ) } initialOpen={ true } >
					<PanelRow>
						<TextControl
							label={__("Quiz ID", "wp_exams")}
							value={quiz_id}
							onChange={quiz_id => setAttributes({ quiz_id })}
						/>
						
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
