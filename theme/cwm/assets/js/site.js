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
	/* ---------- Motion (skipped when the visitor asks for less motion) ---------- */
	var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* Reveal blocks as they scroll into view. Only blocks below the fold are hidden first, and the
	   class is removed once shown so hover effects keep working. */
	if (!calm && 'IntersectionObserver' in window) {
		var sel = '.section-head, .group, .promise, .card, .tile, .tool-link, .deadlines__next, .deadlines__list li, .quote, .stat, .approach__step, .tool, .resource, .cta__copy, .cta__channels li, .featured-insight, .download-feature, .tools-disclaimer, .value, .faq details';
		var fold = window.innerHeight * 0.92;
		var pending = [];
		var show = function (el) {
			el.classList.add('is-in');
			var done = function () { el.classList.remove('reveal', 'is-in'); el.style.removeProperty('--d'); };
			el.addEventListener('transitionend', function te(ev) { if (ev.target === el && ev.propertyName === 'opacity') { el.removeEventListener('transitionend', te); done(); } });
			setTimeout(done, 2000);
		};
		/* Checked on scroll rather than with an observer, so blocks skipped by a fast scroll or a jump link still appear. */
		var check = function () {
			checking = false;
			var h = window.innerHeight * 0.94;
			pending = pending.filter(function (el) {
				if (el.getBoundingClientRect().top < h) { show(el); return false; }
				return true;
			});
			if (!pending.length) window.removeEventListener('scroll', onRevealScroll);
		};
		var checking = false;
		var onRevealScroll = function () { if (!checking) { checking = true; requestAnimationFrame(check); } };
		document.querySelectorAll(sel).forEach(function (el) {
			if (el.closest('.reveal') || el.getBoundingClientRect().top < fold) return;
			var sibs = el.parentElement ? Array.prototype.filter.call(el.parentElement.children, function (c) { return c.matches(sel); }) : [];
			var i = Math.max(0, sibs.indexOf(el));
			el.style.setProperty('--d', Math.min(i, 5) * 80 + 'ms');
			el.classList.add('reveal');
			pending.push(el);
		});
		if (pending.length) { window.addEventListener('scroll', onRevealScroll, { passive: true }); window.addEventListener('resize', onRevealScroll); }

		/* Count up the homepage figures, keeping any prefix or suffix ("250+", "KES 2bn"). */
		var statIO = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (!en.isIntersecting) return;
				statIO.unobserve(en.target);
				var el = en.target, txt = el.textContent, m = txt.match(/^(\D*)([\d,]+(?:\.\d+)?)(.*)$/);
				if (!m) return;
				var to = parseFloat(m[2].replace(/,/g, '')), dec = (m[2].split('.')[1] || '').length, start = null;
				var step = function (t) {
					if (start === null) start = t;
					var p = Math.min(1, (t - start) / 1200), v = to * (1 - Math.pow(1 - p, 3));
					el.textContent = m[1] + v.toLocaleString('en-KE', { minimumFractionDigits: dec, maximumFractionDigits: dec }) + m[3];
					if (p < 1) requestAnimationFrame(step); else el.textContent = txt;
				};
				requestAnimationFrame(step);
			});
		}, { threshold: 0.5 });
		document.querySelectorAll('.stat__figure').forEach(function (el) { statIO.observe(el); });
	}

	/* Reading progress bar on articles. */
	if (document.body.classList.contains('single-post')) {
		var article = document.querySelector('.article__body, .prose, main');
		var bar = document.createElement('div');
		bar.className = 'read-progress';
		bar.setAttribute('aria-hidden', 'true');
		document.body.appendChild(bar);
		var ticking = false;
		var update = function () {
			ticking = false;
			if (!article) return;
			var r = article.getBoundingClientRect();
			var total = r.height - window.innerHeight * 0.6;
			var p = total > 0 ? Math.min(1, Math.max(0, -r.top / total)) : 1;
			bar.style.transform = 'scaleX(' + p + ')';
		};
		window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
		update();
	}
})();
