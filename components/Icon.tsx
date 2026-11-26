import {
  ArrowRight, ArrowUpRight, BookOpen, BriefcaseBusiness, Building2, Calculator, CalendarDays, Check, CircleCheck,
  Clock, Coins, Crosshair, Download, Ear, FileCheck, Gavel, GraduationCap, Handshake, HardHat, HeartHandshake, Hotel,
  Info, Leaf, LifeBuoy, Lightbulb, Mail, MapPin, MessageSquareText, Phone, PiggyBank, ReceiptText, Rocket, Scale,
  Send, ShieldCheck, Sparkles, Sprout, TrendingUp, Wallet, Wheat, type LucideIcon,
} from "lucide-react";

const map: Record<string, LucideIcon> = {
  "arrow-right": ArrowRight, "arrow-up-right": ArrowUpRight, "book-open": BookOpen, "briefcase-business": BriefcaseBusiness,
  "building-2": Building2, calculator: Calculator, calendar: CalendarDays, check: Check, "circle-check": CircleCheck,
  clock: Clock, coins: Coins, crosshair: Crosshair, download: Download, ear: Ear, "file-check": FileCheck, gavel: Gavel,
  "graduation-cap": GraduationCap, handshake: Handshake, "hard-hat": HardHat, "heart-handshake": HeartHandshake, hotel: Hotel,
  info: Info, leaf: Leaf, "life-buoy": LifeBuoy, lightbulb: Lightbulb, mail: Mail, "map-pin": MapPin,
  "message-square-text": MessageSquareText, phone: Phone, "piggy-bank": PiggyBank, "receipt-text": ReceiptText, rocket: Rocket,
  scale: Scale, send: Send, "shield-check": ShieldCheck, sparkles: Sparkles, sprout: Sprout, "trending-up": TrendingUp,
  wallet: Wallet, wheat: Wheat,
};

export function Icon({ name, size = 20, className }: { name: string; size?: number; className?: string }) {
  const C = map[name] || Sparkles;
  return <C size={size} strokeWidth={1.8} className={className} aria-hidden="true" />;
}

export function WhatsAppIcon({ size = 20 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.79-1.47-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.44-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.06 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.48.71.31 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35M12.05 21.5h-.01a9.4 9.4 0 0 1-4.79-1.31l-.34-.2-3.56.93.95-3.47-.22-.36a9.36 9.36 0 0 1-1.44-5.01c0-5.2 4.23-9.42 9.43-9.42 2.52 0 4.88.98 6.66 2.76a9.35 9.35 0 0 1 2.76 6.67c0 5.2-4.23 9.42-9.43 9.42m8.02-17.44A11.27 11.27 0 0 0 12.05.75C5.8.75.72 5.83.72 12.08c0 2 .52 3.95 1.52 5.66L.62 23.25l5.65-1.48a11.3 11.3 0 0 0 5.77 1.47h.01c6.25 0 11.33-5.08 11.33-11.33 0-3.03-1.18-5.87-3.32-8.01" />
    </svg>
  );
}

export function SocialIcon({ kind, size = 18 }: { kind: "linkedin" | "x" | "facebook"; size?: number }) {
  const paths = {
    linkedin: "M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13ZM7.12 20.45H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z",
    x: "M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.21-6.82-5.97 6.82H1.67l7.73-8.84L1.25 2.25h6.83l4.71 6.23 5.45-6.23Zm-1.16 17.52h1.83L7.08 4.13H5.12l11.96 15.64Z",
    facebook: "M24 12.07C24 5.41 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.5 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.5h-2.8V24C19.62 23.1 24 18.1 24 12.07",
  };
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d={paths[kind]} />
    </svg>
  );
}
