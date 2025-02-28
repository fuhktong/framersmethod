import React from "react";
import { Link } from "react-router-dom";
import "./footer.css";

const Footer = () => {
  // First row of social media links
  const firstRowSocial = [
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
  ];

  // Second row of social media links
  const secondRowSocial = [
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

  return (
    <footer>
      <div className="footer-title">
        <a href="/">
          <img src="/framersfooterlogo.png" alt="The Framers' Method" />
        </a>
      </div>

      <div className="footer-container">
        <div>
          <ul className="footer-links">
            <li>
              <Link className="link2" to="/general-caucus">
                GENERAL CAUCUS
              </Link>
            </li>
            <li>
              <Link className="link2" to="/democracy-vs-republic">
                DEMOCRACY vs REPUBLIC
              </Link>
            </li>
            <li>
              <Link className="link2" to="/hamilton-method">
                HAMILTON METHOD
              </Link>
            </li>
            <li>
              <Link className="link2" to="/electors-convention">
                ELECTORS' CONVENTION
              </Link>
            </li>
          </ul>
        </div>
        <div>
          <ul className="footer-links">
            <li>
              <Link className="link2" to="/book">
                BOOK
              </Link>
            </li>
            <li>
              <Link className="link2" to="/faq">
                FAQ
              </Link>
            </li>
            <li>
              <Link className="link2" to="/contribute">
                CONTRIBUTE
              </Link>
            </li>
            <li>
              <Link className="link2" to="/contact">
                CONTACT
              </Link>
            </li>
          </ul>
        </div>
        <div className="footer-social-media">
          <div className="social-row">
            {firstRowSocial.map((social) => (
              <a
                key={social.href}
                href={social.href}
                target="_blank"
                rel="noreferrer"
              >
                <img src={social.src} alt={social.alt} />
              </a>
            ))}
          </div>
          <div className="social-row">
            {secondRowSocial.map((social) => (
              <a
                key={social.href}
                href={social.href}
                target="_blank"
                rel="noreferrer"
              >
                <img src={social.src} alt={social.alt} />
              </a>
            ))}
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
