import { img, type Photo as P } from "@/lib/media";

/** A responsive photo. Decorative unless `alt` text matters for the reader. */
export function Photo({ photo, className, sizes = "(max-width: 700px) 100vw, 50vw", eager = false }: { photo: P; className?: string; sizes?: string; eager?: boolean }) {
  return (
    // eslint-disable-next-line @next/next/no-img-element
    <img
      className={className}
      src={img(photo, 1000)}
      srcSet={`${img(photo, 600)} 600w, ${img(photo, 1000)} 1000w, ${img(photo, 1600)} 1600w`}
      sizes={sizes}
      alt={photo.alt}
      loading={eager ? "eager" : "lazy"}
      decoding="async"
    />
  );
}
