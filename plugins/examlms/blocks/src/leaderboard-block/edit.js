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

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';
import { Icon, check } from '@wordpress/icons';
import ServerSideRender from '@wordpress/server-side-render';
import {
	Disabled,
	TextControl,
	PanelBody,
	PanelRow,
	DatePicker,
	Button, 
	Modal
} from '@wordpress/components';
import { useState } from 'react';
import metadata from './block.json';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit(props) {
	const { attributes, setAttributes } = props;
	var datenow = new Date();
	console.log(attributes);
	let quiz_id = attributes.quiz_id;
	
	const [ startdate, setStartdate ] = useState( attributes.startdate );
	
	//color modal
	const [ isOpen, setOpen ] = useState( false );
	const openModal = () => setOpen( true );
	const closeModal = () => setOpen( false );

	const [ enddate, setEnddate ] = useState( attributes.enddate );
	
	//color modal
	const [ isendOpen, setEndOpen ] = useState( false );
	const endopenModal = () => setEndOpen( true );
	const endcloseModal = () => setEndOpen( false );

	
	const blockProps = useBlockProps( {
		className: 'ldn-wp_exams ldn-wp_exams-numofsales',
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
					<PanelRow>
						<TextControl
							label={__("Start Date", "wp_exams")}
							value={startdate}
							onChange={ ( startdate ) => setStartdate( startdate ) }
							
						/>
						<Button variant="secondary" onClick={ openModal }>
						{__("Pick Date", "wp_exams")}
						</Button>
						{ isOpen && (
							<Modal title={__("Date Picker", "ldninjas-gutenberg-toolkit")} onRequestClose={ closeModal }>
								<DatePicker
									currentDate={ startdate }
									onChange={ ( startdate ) => {
										var newDate = new Date(startdate);
										
										setStartdate( newDate.getFullYear() + '-' + ( newDate.getMonth()+1 ) + '-' + newDate.getDate() ) 
										setAttributes({ startdate: newDate.getFullYear() + '-' + ( newDate.getMonth()+1 ) + '-' + newDate.getDate() })
									} }
									dateOrder = "y-m-d"
								/>
							</Modal>
						) }
					</PanelRow>
					<PanelRow >
						<TextControl
							label={__("End Date", "wp_exams")}
							value={enddate}
							onChange={ ( enddate ) => setEnddate( enddate ) }
							
						/>
						<Button variant="secondary" onClick={ endopenModal }>
						{__("Pick Date", "wp_exams")}
						</Button>
						{ isendOpen && (
							<Modal title={__("Date Picker", "ldninjas-gutenberg-toolkit")} onRequestClose={ endcloseModal }>
								<DatePicker
									currentDate={ enddate }
									onChange={ ( enddate1 ) => {
										var newDate = new Date(enddate1);
										let newenddate = newDate.getFullYear() + '-' + ( newDate.getMonth()+1 ) + '-' + newDate.getDate();
										setEnddate( newenddate );
										setAttributes({ enddate:newenddate })
									} }
									dateOrder = "y-m-d"
								/>
							</Modal>
						) }


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
