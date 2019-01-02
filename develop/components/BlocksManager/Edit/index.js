/* global $ */
import 'jquery-ui/ui/widgets/tabs';
import 'jquery-ui/themes/base/core.css';
import 'jquery-ui/themes/base/theme.css';
import 'jquery-ui/themes/base/tabs.css';

import Dialog from '../../Dialog';
import ClassPicker from './ClassPicker';
import { getPOJO } from '../../../utils';
import BlockRenderer from '../../BlockRenderer';

const { actions, config, lang } = window;

export default function EditBlock() {
	let $block;
	let $dialogEdit;
	let $document;
	const blockData = {
		block: {},
	};

	function getFormData() {
		return getPOJO($dialogEdit.find('#edit_form').serializeArray());
	}

	function getCustomClasses() {
		return $dialogEdit
			.find('#block_class')
			.text()
			.trim();
	}

	function previewBlock() {
		const cssClass = getCustomClasses();
		const formData = getFormData();

		const data = {
			...blockData,
			block: {
				...blockData.block,
				...formData,
				class: cssClass ? ` ${cssClass}` : '',
			},
		};

		BlockRenderer.render($block, data);
	}

	function undoPreviewBlock() {
		BlockRenderer.render($block, blockData);
	}

	function saveForm() {
		const updateSimilar = $dialogEdit
			.dialog('widget')
			.find('#update-similar:checked').length;
		const data = {
			...getFormData(),
			route: config.route,
			similar: updateSimilar,
			class: getCustomClasses(),
		};

		/**
		 * Event to allow other extensions to modify block data before it is saved
		 *
		 * @event blitze_sitemaker_save_block_before
		 * @type {object}
		 * @property {object} data Block data to be saved to db
		 * @since 3.1.2
		 */
		$document.trigger('blitze_sitemaker_save_block_before', [data]);

		$dialogEdit.dialog('close');

		$.post(actions.save_block, data).done(resp => {
			if (resp.list) {
				$.each(resp.list, (i, block) =>
					BlockRenderer.render($(`#block-${block.id}`), { block }),
				);
			}
		});
	}

	function getEditForm(id) {
		$.getJSON(actions.edit_block, { id }).done(row => {
			blockData.block = row;
			if (row.form) {
				const title = row.title.replace(/(<([^>]+)>)/gi, '');

				$dialogEdit.dialog(
					'option',
					'title',
					`${lang.edit} - ${title}`,
				);

				$dialogEdit.html(row.form);
				$dialogEdit.find('#block-settings').tabs();
				$dialogEdit.dialog('open');
			}
		});
	}

	function createContextualSelect() {
		$dialogEdit
			.find('select[data-togglable-settings]')
			.each(function iterator() {
				const $el = $(this);

				$el.change(() => window.phpbb.toggleSelectSettings($el));
				window.phpbb.toggleSelectSettings($el);
			});
	}

	function addBottomPane() {
		$(this)
			.dialog('widget')
			.find('.ui-dialog-buttonpane')
			.prepend(
				`<label class="dialog-check-button">
					<input id="update-similar" type="checkbox" /> 
					${lang.updateSimilar}
				</label>`,
			);
	}

	function getEditDialog() {
		return new Dialog('#dialog-edit', {
			create: addBottomPane,
			beforeClose: () => undoPreviewBlock(),
			buttons: {
				[lang.edit]: function editBtn() {
					$(this).dialog('close');
					saveForm();
				},

				[lang.cancel]: function cancelBtn() {
					$(this).dialog('close');
				},
			},
		});
	}

	$document = $(document);

	$dialogEdit = getEditDialog();

	$dialogEdit.on('change', '.block-preview', () => previewBlock());

	$document.on('click', '.edit-block', e => {
		e.preventDefault();

		$block = $(e.currentTarget).closest('.block');

		const id = $block.attr('id').substring(6);

		getEditForm(id);
		createContextualSelect();
		ClassPicker($dialogEdit);
	});
}
