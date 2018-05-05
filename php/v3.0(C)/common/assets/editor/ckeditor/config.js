/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
  config.toolbar = [
    { name: 'document', items: [ 'Source', 'Print' ] },
    { name: 'editing', items: [ 'Find' ] },
    { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
    { name: 'tools', items: [ 'ShowBlocks', 'Maximize' ] },
    '/',
    { name: 'styles', items: ['Font', 'FontSize' ] },
    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
    { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
    { name: 'links', items: [ 'Link', 'Unlink' ] },
    { name: 'insert', items: [ 'Image', 'Table', 'SpecialChar', 'HorizontalRule' ] },
    { name: 'about', items: [ 'About' ] },
  ];
  if (jtbc.editor.baseHref != null) config.baseHref = jtbc.editor.baseHref;
};