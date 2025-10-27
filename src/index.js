/**
 * WordPress dependencies.
 */
import { addCard } from '@wordpress/icons';

import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies.
 */
import render from './Sidebar';
import './style.scss';

registerPlugin('particles', {
	icon: addCard,
	render,
});
