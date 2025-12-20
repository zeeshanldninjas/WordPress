/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./blocks/src/instructor-block/edit.js":
/*!*********************************************!*\
  !*** ./blocks/src/instructor-block/edit.js ***!
  \*********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ Edit; }
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/server-side-render */ "@wordpress/server-side-render");
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./editor.scss */ "./blocks/src/instructor-block/editor.scss");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./block.json */ "./blocks/src/instructor-block/block.json");

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */


/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */




/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
function Edit({
  attributes,
  setAttributes
}) {
  let userid = attributes.userid;
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.useBlockProps)({
    className: 'ldn-wp_exams ldn-wp_exams-product-rating'
  });
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InspectorControls, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelRow, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("User ID", "wp_exams"),
    value: userid,
    onChange: userid => setAttributes({
      userid
    })
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelRow, null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("(Optional) User id. Default current logged in user's id.", "wp_exams")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...blockProps
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Disabled, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)((_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_3___default()), {
    block: _block_json__WEBPACK_IMPORTED_MODULE_6__.name,
    skipBlockSupportAttributes: true,
    attributes: attributes
  }))));
}

/***/ }),

/***/ "./blocks/src/instructor-block/index.js":
/*!**********************************************!*\
  !*** ./blocks/src/instructor-block/index.js ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./style.scss */ "./blocks/src/instructor-block/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./edit */ "./blocks/src/instructor-block/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./blocks/src/instructor-block/block.json");

/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
const _ = __webpack_require__(/*! lodash */ "lodash");


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */


/**
 * Internal dependencies
 */



/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_4__.name, {
  icon: {
    src: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
      width: "24",
      height: "24",
      viewBox: "0 0 60 60",
      xmlns: "http://www.w3.org/2000/svg"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
      class: "st1",
      d: "M16.1,59.2c-0.2,0-0.4-0.1-0.6-0.2l-3.6-1.9l-3.6,1.9c-0.5,0.3-1,0.2-1.4-0.1c-0.4-0.3-0.6-0.8-0.5-1.3 l0.7-4.1l-3-2.9c-0.4-0.4-0.5-0.9-0.4-1.4c0.2-0.5,0.6-0.9,1.1-0.9l4.1-0.6l1.8-3.7c0.5-0.9,2-0.9,2.5,0l1.8,3.7l4.1,0.6 c0.5,0.1,0.9,0.4,1.1,0.9c0.2,0.5,0,1-0.3,1.4l-3,2.9l0.7,4.1c0.1,0.5-0.1,1-0.5,1.3C16.7,59.1,16.4,59.2,16.1,59.2z M11.8,54.2 c0.2,0,0.4,0,0.6,0.2l1.8,1l-0.3-2c-0.1-0.4,0.1-0.9,0.4-1.2l1.5-1.4l-2-0.3c-0.4-0.1-0.8-0.3-1-0.8l-0.9-1.8l-0.9,1.8 c-0.2,0.4-0.6,0.7-1,0.8l-2,0.3l1.5,1.4c0.3,0.3,0.5,0.8,0.4,1.2l-0.3,2l1.8-1C11.3,54.3,11.6,54.2,11.8,54.2z"
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
      class: "st1",
      d: "M34.2,59.2c-0.2,0-0.4-0.1-0.6-0.2l-3.6-1.9l-3.6,1.9c-0.5,0.3-1,0.2-1.4-0.1c-0.4-0.3-0.6-0.8-0.5-1.3 l0.7-4.1l-3-2.9c-0.4-0.4-0.5-0.9-0.3-1.4c0.2-0.5,0.6-0.9,1.1-0.9l4.1-0.6l1.8-3.7c0.5-0.9,2-0.9,2.5,0l1.8,3.7l4.1,0.6 c0.5,0.1,0.9,0.4,1.1,0.9c0.2,0.5,0,1-0.3,1.4l-3,2.9l0.7,4.1c0.1,0.5-0.1,1-0.5,1.3C34.8,59.1,34.5,59.2,34.2,59.2z M29.9,54.2 c0.2,0,0.4,0,0.6,0.2l1.8,1l-0.4-2c-0.1-0.4,0.1-0.9,0.4-1.2l1.5-1.4l-2-0.3c-0.4-0.1-0.8-0.3-1-0.8l-0.9-1.8L29,49.6 c-0.2,0.4-0.6,0.7-1,0.8l-2,0.3l1.5,1.4c0.3,0.3,0.5,0.8,0.4,1.2l-0.3,2l1.8-1C29.5,54.3,29.7,54.2,29.9,54.2z"
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
      class: "st1",
      d: "M8.1,42.8c0.3-0.5,0.7-1,1.1-1.3c-2.6-3.9-4.1-8.6-4.1-13.6c0-13.7,11.1-24.8,24.8-24.8 c13.7,0,24.8,11.1,24.8,24.8c0,5-1.5,9.7-4.1,13.6c0.5,0.4,0.9,0.8,1.1,1.3l0.5,1.1c3.2-4.5,5.2-10,5.2-16 c0-15.2-12.4-27.5-27.5-27.5C14.7,0.4,2.4,12.7,2.4,27.9c0,6,1.9,11.5,5.2,16L8.1,42.8z"
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
      class: "st1",
      d: "M52.3,59.2c-0.2,0-0.4-0.1-0.6-0.2l-3.6-1.9l-3.6,1.9c-0.5,0.3-1,0.2-1.4-0.1c-0.4-0.3-0.6-0.8-0.5-1.3 l0.7-4.1l-3-2.9c-0.4-0.4-0.5-0.9-0.4-1.4c0.2-0.5,0.6-0.9,1.1-0.9l4.1-0.6l1.8-3.7c0.5-0.9,2-0.9,2.5,0l1.8,3.7l4.1,0.6 c0.5,0.1,0.9,0.4,1.1,0.9c0.2,0.5,0,1-0.3,1.4l-3,2.9l0.7,4.1c0.1,0.5-0.1,1-0.5,1.3C52.9,59.1,52.6,59.2,52.3,59.2z  M48.1,54.2c0.2,0,0.4,0,0.6,0.2l1.8,1l-0.3-2c-0.1-0.4,0.1-0.9,0.4-1.2l1.5-1.4l-2-0.3c-0.4-0.1-0.8-0.3-1-0.8l-0.9-1.8 l-0.9,1.8c-0.2,0.4-0.6,0.7-1,0.8l-2,0.3l1.5,1.4c0.3,0.3,0.5,0.8,0.4,1.2l-0.3,2l1.8-1C47.6,54.3,47.8,54.2,48.1,54.2z"
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
      class: "st1",
      d: "M47.7,28.3c0-0.9-0.4-1.7-1-2.4c0.4-0.6,0.7-1.3,0.7-2.1c0-2-1.7-3.6-3.8-3.6c0,0,0,0,0,0l-10.4,0 c0.2-1.2,0.5-2.7,0.5-3.4c0.2-2.8,0.5-6.3-2.4-6.9c-2.6-0.5-4.3,0.9-5,4.4c-0.5,2.2-1.7,4.4-2.6,5.8c-0.3-0.7-1-1.2-1.8-1.2h-5.2 c-1.1,0-2,0.9-2,2v17.2c0,1.1,0.9,2,2,2h5.2c1.1,0,1.9-0.8,2-1.9c1.2,1.2,3,2.5,5.7,2.5H41c2.1,0,3.8-1.6,3.8-3.6 c0-0.6-0.2-1.1-0.4-1.6c0.9-0.7,1.5-1.7,1.5-2.8c0-0.4-0.1-0.9-0.2-1.3C46.8,30.9,47.7,29.7,47.7,28.3z M19.2,37.1 c-0.9,0-1.7-0.8-1.7-1.7c0-0.9,0.8-1.7,1.7-1.7s1.7,0.8,1.7,1.7C21,36.4,20.2,37.1,19.2,37.1z M43.9,29.1h-1.8l-1.6,0 c-0.8,0-1.4,0.6-1.4,1.4c0,0.8,0.6,1.4,1.4,1.4h1.6c0.6,0,1,0.4,1,0.8c0,0.5-0.5,0.8-1,0.8H41c0,0,0,0,0,0h-1.3 c-0.8,0-1.4,0.6-1.4,1.4c0,0.8,0.6,1.4,1.4,1.4H41c0.6,0,1,0.4,1,0.8c0,0.5-0.5,0.8-1,0.8H29.5c-2.6,0-4.2-2-4.6-2.7V23.4 c0.9-1.2,3.3-4.7,4.1-8.4c0.6-2.5,1.2-2.4,1.9-2.3c0.1,0.1,0.4,0.9,0.1,4c-0.1,0.7-0.5,3.2-0.8,4.7c-0.1,0.4,0,0.8,0.3,1.1 c0.3,0.3,0.6,0.5,1.1,0.5h12.1c0.6,0,1,0.4,1,0.8c0,0.2-0.1,0.4-0.2,0.5c-0.2,0.2-0.5,0.3-0.8,0.3h-2.3c-0.8,0-1.4,0.6-1.4,1.4 c0,0.8,0.6,1.4,1.4,1.4l2.6,0c0.6,0,1,0.4,1,0.8C44.9,28.7,44.5,29.1,43.9,29.1z"
    })))
  },
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_3__["default"]
});

/***/ }),

/***/ "./blocks/src/instructor-block/editor.scss":
/*!*************************************************!*\
  !*** ./blocks/src/instructor-block/editor.scss ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./blocks/src/instructor-block/style.scss":
/*!************************************************!*\
  !*** ./blocks/src/instructor-block/style.scss ***!
  \************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ (function(module) {

module.exports = window["React"];

/***/ }),

/***/ "lodash":
/*!*************************!*\
  !*** external "lodash" ***!
  \*************************/
/***/ (function(module) {

module.exports = window["lodash"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ (function(module) {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ (function(module) {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "@wordpress/server-side-render":
/*!******************************************!*\
  !*** external ["wp","serverSideRender"] ***!
  \******************************************/
/***/ (function(module) {

module.exports = window["wp"]["serverSideRender"];

/***/ }),

/***/ "./blocks/src/instructor-block/block.json":
/*!************************************************!*\
  !*** ./blocks/src/instructor-block/block.json ***!
  \************************************************/
/***/ (function(module) {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"wpe/instructor-block","version":"0.1.0","title":"Instructor - Block","category":"exms-exams-blocks","icon":"smiley","description":"Example block scaffolded with Create Block tool.","example":{},"supports":{"html":false},"textdomain":"wp_exams","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","render":"file:./render.php","viewScript":"file:./view.js"}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	!function() {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = function(result, chunkIds, fn, priority) {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every(function(key) { return __webpack_require__.O[key](chunkIds[j]); })) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"instructor-block/index": 0,
/******/ 			"instructor-block/style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = function(chunkId) { return installedChunks[chunkId] === 0; };
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = function(parentChunkLoadingFunction, data) {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some(function(id) { return installedChunks[id] !== 0; })) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkmultiple_blocks"] = self["webpackChunkmultiple_blocks"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["instructor-block/style-index"], function() { return __webpack_require__("./blocks/src/instructor-block/index.js"); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map