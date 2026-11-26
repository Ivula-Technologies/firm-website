"use client";

import { useState } from "react";
import { PostCard } from "./ui";
import { categories, type Post } from "@/lib/content";

export function InsightsList({ posts }: { posts: Post[] }) {
  const [cat, setCat] = useState("all");
  const keys = Object.keys(categories).filter((k) => posts.some((p) => p.cats.includes(k)));
  const shown = cat === "all" ? posts : posts.filter((p) => p.cats.includes(cat));
  return (
    <>
      <div className="filters" role="toolbar" aria-label="Filter insights">
        <button className={cat === "all" ? "is-on" : ""} aria-pressed={cat === "all"} onClick={() => setCat("all")}>All <span>{posts.length}</span></button>
        {keys.map((k) => (
          <button key={k} className={cat === k ? "is-on" : ""} aria-pressed={cat === k} onClick={() => setCat(k)}>
            {categories[k]} <span>{posts.filter((p) => p.cats.includes(k)).length}</span>
          </button>
        ))}
      </div>
      <div className="posts">
        {shown.map((p, i) => <PostCard key={p.slug} post={p} feature={cat === "all" && i === 0} />)}
      </div>
    </>
  );
}
