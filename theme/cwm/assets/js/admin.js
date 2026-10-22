/* Field framework: media pickers, repeaters, icon previews. */
(function () {
	'use strict';

	document.addEventListener('click', function (e) {
		var t = e.target;

		var choose = t.closest('.cwm-media__choose');
		if (choose) {
			e.preventDefault();
			var box = choose.closest('.cwm-media');
			var lib = box.getAttribute('data-type');
			var frame = wp.media({ title: 'Choose file', multiple: false, library: lib ? { type: lib } : {} });
			frame.on('select', function () {
				var a = frame.state().get('selection').first().toJSON();
				box.querySelector('input[type=hidden]').value = a.id;
				var thumb = a.type === 'image' ? '<img src="' + (a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url) + '" alt="">' : '';
				box.querySelector('.cwm-media__preview').innerHTML = thumb + '<a href="' + a.url + '" target="_blank" rel="noopener">' + a.filename + '</a>';
				box.querySelector('.cwm-media__remove').hidden = false;
			});
			frame.open();
			return;
		}

		var remove = t.closest('.cwm-media__remove');
		if (remove) {
			e.preventDefault();
			var m = remove.closest('.cwm-media');
			m.querySelector('input[type=hidden]').value = '';
			m.querySelector('.cwm-media__preview').innerHTML = '<em>No file selected</em>';
			remove.hidden = true;
			return;
		}

		var add = t.closest('.cwm-repeater__add');
		if (add) {
			e.preventDefault();
			var rep = add.closest('.cwm-repeater');
			var rows = rep.querySelector('.cwm-repeater__rows');
			var max = parseInt(rep.getAttribute('data-max'), 10) || 0;
			if (max && rows.children.length >= max) {
				window.alert('You can add up to ' + max + '.');
				return;
			}
			var tpl = rep.querySelector('.cwm-repeater__tpl').innerHTML;
			var idx = Date.now();
			var wrap = document.createElement('div');
			wrap.innerHTML = tpl.replace(/__i__/g, idx);
			rows.appendChild(wrap.firstElementChild);
			return;
		}

		var row = t.closest('.cwm-repeater__row');
		if (!row) return;
		if (t.closest('.cwm-repeater__remove')) {
			e.preventDefault();
			if (window.confirm('Remove this row?')) row.remove();
		} else if (t.closest('.cwm-repeater__up')) {
			e.preventDefault();
			if (row.previousElementSibling) row.parentNode.insertBefore(row, row.previousElementSibling);
		} else if (t.closest('.cwm-repeater__down')) {
			e.preventDefault();
			if (row.nextElementSibling) row.parentNode.insertBefore(row.nextElementSibling, row);
		}
	});

	// Live icon preview.
	document.addEventListener('change', function (e) {
		if (e.target.classList.contains('cwm-icon-select')) {
			var p = e.target.parentNode.querySelector('.cwm-icon-preview');
			if (p && window.cwmIcons && window.cwmIcons[e.target.value]) p.innerHTML = window.cwmIcons[e.target.value];
		}
	});
})();
