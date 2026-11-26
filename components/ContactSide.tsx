import { Icon, WhatsAppIcon } from "./Icon";
import { MapEmbed } from "./MapEmbed";
import { site, telUrl, whatsappUrl } from "@/lib/site";

export function ContactSide() {
  return (
    <div className="contact__side">
      <a className="ccard" href={telUrl()}>
        <span className="ccard__icon"><Icon name="phone" size={20} /></span>
        <span><strong>Call us</strong><span>{site.phone}</span></span>
      </a>
      <a className="ccard ccard--wa" href={whatsappUrl()} target="_blank" rel="noopener">
        <span className="ccard__icon"><WhatsAppIcon size={20} /></span>
        <span><strong>WhatsApp</strong><span>Chat with us now</span></span>
      </a>
      <a className="ccard" href={`mailto:${site.email}`}>
        <span className="ccard__icon"><Icon name="mail" size={20} /></span>
        <span><strong>Email</strong><span>{site.email}</span></span>
      </a>
      <div className="ccard">
        <span className="ccard__icon"><Icon name="clock" size={20} /></span>
        <span><strong>Office hours</strong><span>{site.hours.join(". ")}</span></span>
      </div>
      <MapEmbed query={site.mapQuery} />
    </div>
  );
}
