import React from "react";
import { Link } from "react-router-dom";
import "./footer.css";

const Footer = () => {
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
          <a
            href="https://www.instagram.com/framersmethod/"
            target="_blank"
            rel="noreferrer"
          >
            <img
              src="whitelogoinsta.png"
              alt="The Framers' Method on Instagram"
            />
          </a>
          <a
            href="https://bsky.app/profile/framersmethod.bsky.social"
            target="_blank"
            rel="noreferrer"
          >
            <img
              src="whitelogobluesky.png"
              alt="The Framers' Method on Bluesky"
            />
          </a>
          <a
            href="https://twitter.com/framersmethod"
            target="_blank"
            rel="noreferrer"
          >
            <img src="whitelogox.png" alt="The Framers' Method on Twitter" />
          </a>
          <a
            href="https://medium.com/@framersmethod"
            target="_blank"
            rel="noreferrer"
          >
            <img src="whitelogomedium.png" alt="The Framers' Method - Medium" />
          </a>
          <a
            href="https://substack.com/@framersmethod"
            target="_blank"
            rel="noreferrer"
          >
            <img
              src="whitelogosubstack.png"
              alt="The Framers' Method - Substack"
            />
          </a>
          <a
            href="https://www.youtube.com/@framersmethod/featured"
            target="_blank"
            rel="noreferrer"
          >
            <img
              src="whitelogoyoutube.png"
              alt="The Framers' Method on YouTube"
            />
          </a>
          <a
            href="https://www.tiktok.com/@framersmethod"
            target="_blank"
            rel="noreferrer"
          >
            <img
              src="whitelogotiktok.png"
              alt="The Framers' Method on TikTok"
            />
          </a>
          <a
            href="https://www.patreon.com/framersmethod"
            target="_blank"
            rel="noreferrer"
          >
            <img
              src="whitelogopatreon.png"
              alt="The Framers' Method on Patreon"
            />
          </a>
          <a href="https://a.co/d/0dimzJAr" target="_blank" rel="noreferrer">
            <img
              src="whitelogoamazon.png"
              alt="On The Framers' Method Book - Amazon"
            />
          </a>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
