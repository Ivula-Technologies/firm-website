/* Free tools: PAYE, VAT and the ESG readiness check. Rates come from Firm settings (window.cwmRates). */
(function () {
	'use strict';

	var R = window.cwmRates || {};
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var nf = new Intl.NumberFormat('en-KE', { maximumFractionDigits: 0 });

	function kes(n) { return 'KES ' + nf.format(Math.round(n)); }
	function num(v) { var n = parseFloat(String(v).replace(/,/g, '')); return isFinite(n) && n > 0 ? n : 0; }
	function round2(n) { return Math.round(n * 100) / 100; }

	/* Animate a number shown in an element, from what it showed last to the new value. */
	function tween(el, to, fmt) {
		if (!el) { return; }
		var from = parseFloat(el.getAttribute('data-v') || '0');
		el.setAttribute('data-v', to);
		if (reduce || from === to) { el.textContent = fmt(to); return; }
		var start = null, dur = 450;
		if (el._raf) { cancelAnimationFrame(el._raf); }
		function step(t) {
			if (start === null) { start = t; }
			var p = Math.min(1, (t - start) / dur);
			var e = 1 - Math.pow(1 - p, 3);
			el.textContent = fmt(from + (to - from) * e);
			if (p < 1) { el._raf = requestAnimationFrame(step); }
		}
		el._raf = requestAnimationFrame(step);
	}

	/* ---------- PAYE ---------- */
	function paye(gross) {
		var rate = (R.nssf || 0) / 100;
		var lel = R.lel || 0, uel = R.uel || 0;
		var nssf = rate * Math.min(gross, lel) + rate * Math.max(0, Math.min(gross, uel) - lel);
		var shif = gross > 0 ? Math.max(R.shifMin || 0, gross * (R.shif || 0) / 100) : 0;
		var ahl = gross * (R.ahl || 0) / 100;
		var taxable = Math.max(0, gross - nssf - shif - ahl);
		var tax = 0, lower = 0, bands = R.bands || [];
		for (var i = 0; i < bands.length; i++) {
			var upper = bands[i].upto === null ? Infinity : bands[i].upto;
			if (taxable > lower) { tax += (Math.min(taxable, upper) - lower) * bands[i].rate / 100; }
			lower = upper;
			if (taxable <= upper) { break; }
		}
		var p = Math.max(0, tax - (R.relief || 0));
		var net = gross - nssf - shif - ahl - p;
		return { gross: gross, nssf: round2(nssf), shif: round2(shif), ahl: round2(ahl), paye: round2(p), net: round2(net), employer: round2(gross + nssf + ahl) };
	}

	function initPaye(form) {
		var input = form.querySelector('#paye-gross');
		var range = form.querySelector('#paye-range');
		var out = function (k) { return form.querySelector('[data-out="' + k + '"]'); };
		var bar = function (k) { return form.querySelector('[data-bar="' + k + '"]'); };

		function render() {
			var g = num(input.value);
			var r = paye(g);
			tween(out('net'), r.net, kes);
			['paye', 'nssf', 'shif', 'ahl', 'employer'].forEach(function (k) { tween(out(k), r[k], function (v) { return nf.format(Math.round(v)); }); });
			tween(out('annual'), r.net * 12, kes);
			var rate = g ? (r.paye / g) * 100 : 0;
			tween(out('rate'), rate, function (v) { return v.toFixed(1) + '%'; });
			var stat = r.nssf + r.shif + r.ahl;
			var tot = g || 1;
			bar('net').style.width = Math.max(0, r.net / tot * 100) + '%';
			bar('paye').style.width = (r.paye / tot * 100) + '%';
			bar('stat').style.width = (stat / tot * 100) + '%';
		}

		input.addEventListener('input', function () {
			var g = num(input.value);
			if (range) { range.value = Math.min(range.max, Math.max(range.min, g)); fill(range); }
			render();
		});
		if (range) {
			range.addEventListener('input', function () { input.value = range.value; fill(range); render(); });
		}
		form.querySelectorAll('[data-set]').forEach(function (b) {
			b.addEventListener('click', function () {
				input.value = b.getAttribute('data-set');
				input.dispatchEvent(new Event('input'));
			});
		});
		form.addEventListener('submit', function (e) { e.preventDefault(); });
		if (range) { fill(range); }
		render();
	}

	/* Colour the slider track up to the thumb. */
	function fill(range) {
		var p = (range.value - range.min) / (range.max - range.min) * 100;
		range.style.setProperty('--fill', p + '%');
	}

	/* ---------- VAT ---------- */
	function initVat(form) {
		var input = form.querySelector('#vat-amount');
		var rate = (R.vat || 0) / 100;
		var out = function (k) { return form.querySelector('[data-out="' + k + '"]'); };
		var money = function (v) { return nf.format(Math.round(v)); };
		out('vatrate').textContent = (R.vat || 0) + '%';

		function render() {
			var a = num(input.value);
			var mode = (form.querySelector('input[name="vat-mode"]:checked') || {}).value;
			var net = mode === 'remove' ? a / (1 + rate) : a;
			var vat = net * rate;
			tween(out('net'), net, money);
			tween(out('vat'), vat, money);
			tween(out('gross'), net + vat, money);
		}
		form.addEventListener('input', render);
		form.addEventListener('change', render);
		form.addEventListener('submit', function (e) { e.preventDefault(); });
		render();
	}

	/* ---------- ESG readiness ---------- */
	var BANDS = [
		{ max: 4, name: 'Getting started', text: 'You have the basics to put in place. That is normal for most Kenyan businesses today, and a few steps make a big difference.' },
		{ max: 8, name: 'Building up', text: 'Good foundations. Close the gaps below and you will be able to answer most lender and customer questions with confidence.' },
		{ max: 12, name: 'Report-ready', text: 'You are well placed to publish an ESG report or a sustainability section in your annual report.' }
	];

	function initEsg(form) {
		var qs = form.querySelectorAll('.quiz__q');
		var total = qs.length * 2;
		var ring = form.querySelector('.ring__bar');
		var circ = 2 * Math.PI * 52;
		var out = function (k) { return form.querySelector('[data-out="' + k + '"]'); };
		var cta = form.querySelector('.quiz__cta');
		if (ring) { ring.style.strokeDasharray = circ; ring.style.strokeDashoffset = circ; }

		function render() {
			var score = 0, answered = 0, tips = [];
			qs.forEach(function (q) {
				var c = q.querySelector('input:checked');
				q.classList.toggle('is-answered', !!c);
				if (!c) { return; }
				answered++;
				score += parseInt(c.value, 10);
				if (c.value !== '2') { tips.push({ t: q.getAttribute('data-tip'), w: c.value === '0' ? 0 : 1 }); }
			});
			tween(out('score'), score, function (v) { return String(Math.round(v)); });
			out('answered').textContent = answered;
			if (ring) {
				ring.style.strokeDashoffset = circ * (1 - score / total);
				form.querySelector('.ring').setAttribute('data-level', score <= 4 ? 'low' : score <= 8 ? 'mid' : 'high');
			}
			if (answered < qs.length) {
				out('band').textContent = answered ? 'Keep going: ' + (qs.length - answered) + ' to go' : 'Answer the questions to see your score';
				return;
			}
			var band = BANDS.filter(function (b) { return score <= b.max; })[0] || BANDS[2];
			out('band').textContent = band.name;
			out('advice').textContent = band.text;
			var list = out('steps');
			list.innerHTML = '';
			tips.sort(function (a, b) { return a.w - b.w; }).slice(0, 3).forEach(function (tip) {
				var li = document.createElement('li');
				li.textContent = tip.t;
				list.appendChild(li);
			});
			if (!tips.length) {
				var li = document.createElement('li');
				li.textContent = 'Have your data checked independently before you publish, so lenders and investors can rely on it.';
				list.appendChild(li);
			}
			if (cta) { cta.hidden = false; }
			form.classList.add('is-done');
		}
		form.addEventListener('change', render);
		form.addEventListener('submit', function (e) { e.preventDefault(); });
		render();
	}

	/* ---------- Deadline counters ---------- */
	function countUp(el) {
		var to = parseInt(el.getAttribute('data-count'), 10) || 0;
		if (reduce || to === 0) { return; }
		el.textContent = '0';
		var start = null, dur = 900;
		function step(t) {
			if (start === null) { start = t; }
			var p = Math.min(1, (t - start) / dur);
			el.textContent = String(Math.round(to * (1 - Math.pow(1 - p, 3))));
			if (p < 1) { requestAnimationFrame(step); }
		}
		requestAnimationFrame(step);
	}

	/* ---------- KRA deadlines: recount from today's date in Nairobi ---------- */
	function nairobiToday() {
		try {
			var parts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Africa/Nairobi', year: 'numeric', month: 'numeric', day: 'numeric' }).formatToParts(new Date());
			var get = function (t) { return parseInt(parts.filter(function (p) { return p.type === t; })[0].value, 10); };
			return Date.UTC(get('year'), get('month') - 1, get('day'));
		} catch (e) {
			var d = new Date(Date.now() + 3 * 3600000);
			return Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate());
		}
	}
	var DAY = 86400000;
	function fmt(t, opts) { return new Date(t).toLocaleDateString('en-GB', Object.assign({ timeZone: 'UTC' }, opts)).replace(/,/g, ''); }
	function phrase(n) { return n === 0 ? 'today' : n === 1 ? 'tomorrow' : 'in ' + n + ' days'; }

	function initDeadlines(box) {
		var today = nairobiToday();
		var ty = new Date(today).getUTCFullYear(), tm = new Date(today).getUTCMonth() + 1;
		var list = box.querySelector('.deadlines__list');
		var items = Array.prototype.map.call(list.querySelectorAll('li[data-day]'), function (li) {
			var day = parseInt(li.getAttribute('data-day'), 10);
			var months = li.getAttribute('data-months').split(',').map(Number);
			var y = ty, m = tm, when = null;
			for (var i = 0; i < 25 && when === null; i++) {
				if (months.indexOf(m) > -1) {
					var t = Date.UTC(y, m - 1, day);
					if (t >= today) { when = t; }
				}
				if (++m > 12) { m = 1; y++; }
			}
			return { li: li, when: when, days: Math.round((when - today) / DAY) };
		}).filter(function (x) { return x.when !== null; });
		if (!items.length) { return; }
		items.sort(function (a, b) { return a.days - b.days; });
		var limit = parseInt(box.getAttribute('data-limit'), 10) || 0;
		items.forEach(function (it, i) {
			it.li.hidden = i === 0 || (limit > 0 && i > limit);
			it.li.querySelector('.deadlines__short').textContent = new Date(it.when).getUTCDate() + ' ' + 'Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec'.split(' ')[new Date(it.when).getUTCMonth()];
			it.li.querySelector('.deadlines__when small').textContent = phrase(it.days);
			list.appendChild(it.li);
		});
		var n = items[0], q = function (s) { return box.querySelector(s); };
		q('.deadlines__num').textContent = n.days;
		q('.deadlines__num').setAttribute('data-count', n.days);
		q('.deadlines__unit').textContent = n.days === 1 ? 'day' : 'days';
		q('.deadlines__name').textContent = n.li.getAttribute('data-name');
		q('.deadlines__day').textContent = fmt(n.when, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
		var wd = new Date(n.when).getUTCDay(), flag = q('.deadlines__flag');
		flag.hidden = !(wd === 0 || wd === 6);
		flag.textContent = flag.hidden ? '' : 'Falls on a ' + (wd === 0 ? 'Sunday' : 'Saturday') + ': file early';
	}
	document.querySelectorAll('.deadlines').forEach(initDeadlines);

	document.querySelectorAll('[data-tool="paye"]').forEach(initPaye);
	document.querySelectorAll('[data-tool="vat"]').forEach(initVat);
	document.querySelectorAll('[data-tool="esg"]').forEach(initEsg);

	var nums = document.querySelectorAll('.deadlines__num[data-count]');
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) { countUp(en.target); io.unobserve(en.target); }
			});
		}, { threshold: 0.6 });
		nums.forEach(function (n) { io.observe(n); });
	}
})();
