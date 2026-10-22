/* Count What Matters: small, dependency-free front-end behaviour. */
(function () {
	'use strict';
	var doc = document.documentElement;
	var header = document.getElementById('site-header');

	// Sticky header shadow.
	var onScroll = function () {
		if (header) header.classList.toggle('is-scrolled', window.scrollY > 8);
	};
	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	// Mobile menu.
	var toggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('main-nav');
	function setNav(open) {
		if (!toggle) return;
		if (open && header) {
			doc.style.setProperty('--nav-top', Math.max(0, header.getBoundingClientRect().bottom) + 'px');
		}
		document.body.classList.toggle('nav-open', open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
	}
	if (toggle) {
		toggle.addEventListener('click', function () {
			setNav(!document.body.classList.contains('nav-open'));
		});
	}
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			setNav(false);
			document.querySelectorAll('.main-nav__item.is-open').forEach(function (li) {
				li.classList.remove('is-open');
				var b = li.querySelector('.submenu-toggle');
				if (b) b.setAttribute('aria-expanded', 'false');
			});
		}
	});
	window.addEventListener('resize', function () {
		if (window.innerWidth > 1100) setNav(false);
	});

	// Submenus (click/tap and keyboard).
	document.querySelectorAll('.submenu-toggle').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var li = btn.closest('.main-nav__item');
			var open = !li.classList.contains('is-open');
			document.querySelectorAll('.main-nav__item.is-open').forEach(function (o) {
				if (o !== li) {
					o.classList.remove('is-open');
					o.querySelector('.submenu-toggle').setAttribute('aria-expanded', 'false');
				}
			});
			li.classList.toggle('is-open', open);
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	});
	document.addEventListener('click', function (e) {
		if (window.innerWidth > 1100 && !e.target.closest('.main-nav__item')) {
			document.querySelectorAll('.main-nav__item.is-open').forEach(function (o) {
				o.classList.remove('is-open');
			});
		}
	});

	// Filters submit on change.
	document.querySelectorAll('.js-autosubmit').forEach(function (s) {
		s.addEventListener('change', function () {
			s.form.submit();
		});
	});

	// Copy link.
	document.querySelectorAll('.share__copy').forEach(function (b) {
		b.addEventListener('click', function () {
			var url = b.getAttribute('data-url');
			var done = function () {
				b.classList.add('is-copied');
				b.setAttribute('aria-label', 'Link copied');
				setTimeout(function () {
					b.classList.remove('is-copied');
					b.setAttribute('aria-label', 'Copy link');
				}, 2000);
			};
			if (navigator.clipboard) {
				navigator.clipboard.writeText(url).then(done);
			} else {
				window.prompt('Copy this link', url);
			}
		});
	});

	// Click-to-load map.
	document.querySelectorAll('.map__load').forEach(function (b) {
		b.addEventListener('click', function () {
			var m = b.closest('.map');
			var f = document.createElement('iframe');
			f.src = m.getAttribute('data-src');
			f.loading = 'lazy';
			f.title = 'Map';
			f.referrerPolicy = 'no-referrer-when-downgrade';
			m.innerHTML = '';
			m.appendChild(f);
		});
	});

	// Glossary filter.
	var gf = document.querySelector('[data-glossary-filter]');
	if (gf) {
		gf.addEventListener('input', function () {
			var q = gf.value.trim().toLowerCase();
			document.querySelectorAll('.glossary__letter').forEach(function (letter) {
				var any = false;
				letter.querySelectorAll('.glossary__term').forEach(function (t) {
					var hit = !q || t.textContent.toLowerCase().indexOf(q) !== -1;
					t.hidden = !hit;
					any = any || hit;
				});
				letter.hidden = !any;
			});
		});
	}

	// Forms: friendly validation before submit.
	document.querySelectorAll('.cwm-form').forEach(function (form) {
		form.addEventListener('submit', function (e) {
			var bad = null;
			form.querySelectorAll('[aria-invalid]').forEach(function (el) {
				el.removeAttribute('aria-invalid');
			});
			form.querySelectorAll('input[required], select[required], textarea[required]').forEach(function (el) {
				var ok = el.type === 'checkbox' ? el.checked : el.value.trim() !== '';
				if (ok && el.type === 'email') ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value.trim());
				if (!ok) {
					el.setAttribute('aria-invalid', 'true');
					bad = bad || el;
				}
			});
			form.querySelectorAll('fieldset').forEach(function (fs) {
				var legend = fs.querySelector('.req');
				if (legend && !fs.querySelector('input:checked')) {
					var first = fs.querySelector('input');
					if (first) {
						first.closest('.check').setAttribute('aria-invalid', 'true');
						bad = bad || first;
					}
				}
			});
			var file = form.querySelector('input[type=file]');
			if (file && file.files && file.files[0] && file.files[0].size > parseInt(file.getAttribute('data-max'), 10)) {
				window.alert('The attachment is larger than 10 MB. Please attach a smaller file or email it to us.');
				bad = bad || file;
			}
			if (bad) {
				e.preventDefault();
				bad.focus();
				return;
			}
			var btn = form.querySelector('button[type=submit]');
			if (btn) {
				btn.disabled = true;
				btn.setAttribute('aria-busy', 'true');
			}
		});
	});
})();
