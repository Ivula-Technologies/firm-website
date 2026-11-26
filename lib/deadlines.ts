/** KRA deadlines, counted from today's date in Nairobi. Runs on the server and in the browser. */
export type Deadline = { name: string; note: string; icon: string; when: number; days: number };

const RULES = [
  { name: "PAYE, SHIF, housing levy and NSSF", note: "Pay and file for last month's payroll.", day: 9, months: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], icon: "wallet" },
  { name: "VAT, turnover tax and excise duty", note: "Returns and payment for last month.", day: 20, months: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], icon: "receipt-text" },
  { name: "Instalment tax", note: "One of four payments in the year, for a December year-end.", day: 20, months: [4, 6, 9, 12], icon: "coins" },
  { name: "Annual income tax returns", note: "Individuals, and companies with a December year-end.", day: 30, months: [6], icon: "file-check" },
];

const DAY = 86400000;

export function nairobiToday(now = new Date()) {
  const parts = new Intl.DateTimeFormat("en-GB", { timeZone: "Africa/Nairobi", year: "numeric", month: "numeric", day: "numeric" }).formatToParts(now);
  const get = (t: string) => parseInt(parts.find((p) => p.type === t)!.value, 10);
  return Date.UTC(get("year"), get("month") - 1, get("day"));
}

export function upcomingDeadlines(today = nairobiToday()): Deadline[] {
  const d = new Date(today);
  const out: Deadline[] = [];
  for (const r of RULES) {
    let y = d.getUTCFullYear();
    let m = d.getUTCMonth() + 1;
    for (let i = 0; i < 25; i++) {
      if (r.months.includes(m)) {
        const t = Date.UTC(y, m - 1, r.day);
        if (t >= today) {
          out.push({ name: r.name, note: r.note, icon: r.icon, when: t, days: Math.round((t - today) / DAY) });
          break;
        }
      }
      if (++m > 12) {
        m = 1;
        y++;
      }
    }
  }
  return out.sort((a, b) => a.days - b.days);
}

const MONTHS = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
const DAYS = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
export function longDate(t: number) {
  const d = new Date(t);
  return `${DAYS[d.getUTCDay()]} ${d.getUTCDate()} ${MONTHS[d.getUTCMonth()]} ${d.getUTCFullYear()}`;
}
export function shortDate(t: number) {
  const d = new Date(t);
  return `${d.getUTCDate()} ${MONTHS[d.getUTCMonth()].slice(0, 3)}`;
}
export function phrase(n: number) {
  return n === 0 ? "today" : n === 1 ? "tomorrow" : `in ${n} days`;
}
