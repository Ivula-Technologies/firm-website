/**
 * Photos used across the site. All are free Unsplash photos (Unsplash License:
 * free for commercial use, no permission needed). They are served from Unsplash's
 * image CDN. To use the client's own photos, put files in public/media/ and change
 * `src` to "/media/<file>.jpg" (the size parameters are then ignored).
 */
export type Photo = { src: string; alt: string; credit: string; page: string };

const u = (id: string) => `https://images.unsplash.com/photo-${id}`;

export const photos = {
  nairobi: { src: u("1741991110666-88115e724741"), alt: "Nairobi city skyline on a sunny day", credit: "imsogabriel stock", page: "https://unsplash.com/photos/IaJm3mq0F5o" },
  nairobiTowers: { src: u("1693902997450-7e912c0d3554"), alt: "Office towers in Nairobi", credit: "Kenny Murgor", page: "https://unsplash.com/photos/vyagy6HJpVE" },
  handshake: { src: u("1521791136064-7986c2920216"), alt: "Two people shaking hands across a desk", credit: "Cytonn Photography", page: "https://unsplash.com/photos/n95VMLxqM2I" },
  reviewing: { src: u("1772588627327-1eeddcf73c11"), alt: "Reviewing documents with a calculator and laptop", credit: "Kelly Sikkema", page: "https://unsplash.com/photos/0oZpRxG5Hkk" },
  deskWork: { src: u("1758876202610-bae5608f5051"), alt: "A woman working at a desk in an office", credit: "Vitaly Gariev", page: "https://unsplash.com/photos/-ZRtrH-RubE" },
  papers: { src: u("1771758249853-415175dc29b9"), alt: "A stack of papers", credit: "Camilo Rueda López", page: "https://unsplash.com/photos/3CLPBgNuX40" },
  signing: { src: u("1764231467852-b609a742e082"), alt: "Hands signing a document on a desk", credit: "Jakub Żerdzicki", page: "https://unsplash.com/photos/WTgGFO_gGW8" },
  greenCity: { src: u("1781504798134-53a5a833c34f"), alt: "City buildings among green trees", credit: "Seth Salimo", page: "https://unsplash.com/photos/i0uxlHOhb1Y" },
  shopOwner: { src: u("1687422808248-f807f4ea2a2e"), alt: "A business owner in an apron checking his phone", credit: "Ali Mkumbwa", page: "https://unsplash.com/photos/uk3ey_vhDKA" },
  teamCouch: { src: u("1655720357872-ce227e4164ba"), alt: "A team looking at a laptop together", credit: "Iwaria Inc.", page: "https://unsplash.com/photos/M7ALc3UuX_g" },
  teamTable: { src: u("1573164574572-cb89e39749b4"), alt: "A group working with laptops around a table", credit: "Christina @ wocintechchat.com", page: "https://unsplash.com/photos/faEfWCdOKIg" },
  professional: { src: u("1622295023825-6e319464b810"), alt: "A professional holding a document", credit: "Emmanuel Ikwuegbu", page: "https://unsplash.com/photos/h5xEHzfepNk" },
  classroom: { src: u("1632215861513-130b66fe97f4"), alt: "A teacher in front of a class of children", credit: "Emmanuel Ikwuegbu", page: "https://unsplash.com/photos/VC6MGt9ZoBA" },
  farmer: { src: u("1741874299706-2b8e16839aaa"), alt: "A farmer tending crops in a green field", credit: "Richard Nyoni", page: "https://unsplash.com/photos/1AoGjqdyDLU" },
  market: { src: u("1777065851469-71aef898a26f"), alt: "A busy outdoor market", credit: "Wietse Jongsma", page: "https://unsplash.com/photos/-OAYEZu641U" },
  laptopBraids: { src: u("1604933762021-54a5858c9832"), alt: "A woman working on a laptop in a workspace", credit: "Daniel Thomas", page: "https://unsplash.com/photos/HA-0i0E7sq4" },
  meetingRoom: { src: u("1573164574511-73c773193279"), alt: "Colleagues talking in a meeting room", credit: "Christina @ wocintechchat.com", page: "https://unsplash.com/photos/rg1y72eKw6o" },
} satisfies Record<string, Photo>;

export const servicePhoto: Record<string, Photo> = {
  "accounting-and-bookkeeping": photos.reviewing,
  "payroll-and-hr-support": photos.deskWork,
  tax: photos.papers,
  "company-secretarial-and-compliance": photos.signing,
  "business-advisory": photos.handshake,
  "esg-and-sustainability-advisory": photos.greenCity,
};

export const industryPhoto: Record<string, Photo> = {
  "SMEs and startups": photos.shopOwner,
  "NGOs and non-profits": photos.teamCouch,
  "SACCOs and cooperatives": photos.teamTable,
  "Real estate and construction": photos.nairobiTowers,
  "Professional firms": photos.professional,
  "Schools and religious institutions": photos.classroom,
  Hospitality: photos.market,
  Agribusiness: photos.farmer,
};

/** A sized URL for Unsplash photos; local files are returned unchanged. */
export function img(p: Photo, w: number) {
  return p.src.startsWith("https://images.unsplash.com/") ? `${p.src}?w=${w}&q=70&auto=format&fit=crop` : p.src;
}
