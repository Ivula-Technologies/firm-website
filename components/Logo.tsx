import { site } from "@/lib/site";

/** The JENIQA mark: a ring with a rising arrow breaking out of it. */
export function Mark({ light = false, size = 40 }: { light?: boolean; size?: number }) {
  return (
    <svg className="logo__mark" width={size} height={size} viewBox="28 28 416 416" aria-hidden="true" focusable="false">
      <circle cx="222" cy="222" r="170" fill="none" stroke={light ? "#fff" : "#0E2747"} strokeWidth="38" />
      <path
        d="M120 290 188 232 238 264 408 95M330 86h88v86"
        fill="none"
        stroke={light ? "#7FAEF2" : "#2160C4"}
        strokeWidth="40"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

export function Logo({ light = false }: { light?: boolean }) {
  const name: string = site.brand;
  const i = name.indexOf(site.brandAccent);
  return (
    <span className={`logo${light ? " logo--light" : ""}`}>
      <Mark light={light} />
      <span className="logo__word">
        {i < 0 ? name : (
          <>
            {name.slice(0, i)}
            <span className="logo__accent">{site.brandAccent}</span>
            {name.slice(i + site.brandAccent.length)}
          </>
        )}
      </span>
    </span>
  );
}
