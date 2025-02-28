import React, { useEffect, useState } from "react";
import "./socialmediabar.css";

export const SocialMediaBar = () => {
  const [isMobile, setIsMobile] = useState(window.innerWidth <= 650);

  useEffect(() => {
    const handleResize = () => {
      setIsMobile(window.innerWidth <= 650);
    };

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  const allLinks = [
    {
      href: "https://www.instagram.com/framersmethod/",
      src: "whitelogoinsta.png",
      alt: "The Framers' Method on Instagram",
    },
    {
      href: "https://bsky.app/profile/framersmethod.bsky.social",
      src: "whitelogobluesky.png",
      alt: "The Framers' Method on Bluesky",
    },
    {
      href: "https://twitter.com/framersmethod",
      src: "whitelogox.png",
      alt: "The Framers' Method on Twitter",
    },
    {
      href: "https://medium.com/@framersmethod",
      src: "whitelogomedium.png",
      alt: "The Framers' Method - Medium",
    },
    {
      href: "https://substack.com/@framersmethod",
      src: "whitelogosubstack.png",
      alt: "The Framers' Method - Substack",
    },
    {
      href: "https://www.youtube.com/@framersmethod/featured",
      src: "whitelogoyoutube.png",
      alt: "The Framers' Method on YouTube",
    },
    {
      href: "https://www.tiktok.com/@framersmethod",
      src: "whitelogotiktok.png",
      alt: "The Framers' Method on TikTok",
    },
    {
      href: "https://www.patreon.com/framersmethod",
      src: "whitelogopatreon.png",
      alt: "The Framers' Method on Patreon",
    },
    {
      href: "https://a.co/d/0dimzJAr",
      src: "whitelogoamazon.png",
      alt: "On The Framers' Method Book - Amazon",
    },
  ];

  const midpoint = Math.ceil(allLinks.length / 2);
  const firstRowLinks = allLinks.slice(0, midpoint);
  const secondRowLinks = allLinks.slice(midpoint);

  const renderSocialLink = (link) => (
    <a key={link.href} href={link.href} target="_blank" rel="noreferrer">
      <img src={link.src} alt={link.alt} />
    </a>
  );

  if (isMobile) {
    return (
      <section className="socialmediabar">
        <div className="socialmediabar-row">
          {firstRowLinks.map(renderSocialLink)}
        </div>
        <div className="socialmediabar-row">
          {secondRowLinks.map(renderSocialLink)}
        </div>
      </section>
    );
  }

  return (
    <section className="socialmediabar">
      {allLinks.map(renderSocialLink)}
    </section>
  );
};
