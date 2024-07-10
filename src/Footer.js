import React from 'react';
import { Link } from 'react-router-dom';
import './Footer.css';

const Footer = () => {
    return (
        <footer>
            <a href="/"><h2>The Framers' Method</h2></a>
            <div className="footer-container">
                <div>
                    <ul className="footer-links">
                        <li><Link className="link2" to="/news">News</Link></li>
                        <li><Link className="link2" to="/archive">Archive</Link></li>
                        <li><Link className="link2" to="/repvsdem">Republic vs Democracy</Link></li>
                        <li><Link className="link2" to="/hamilton">Hamilton Method</Link></li>
                        <li><Link className="link2" to="/electors">Electors Convention</Link></li>
                    </ul>
                </div>
                <div>
                    <ul className="footer-links">
                        <li><Link className="link2" to="/book">Book</Link></li>
                        <li><Link className="link2" to="/faq">FAQ</Link></li>
                        <li><Link className="link2" to="/contribute">Contribute</Link></li>
                        <li><Link className="link2" to="/contact">Contact</Link></li>
                    </ul>
                </div>
                <div className="footer-social-media">
                    <a href="https://www.instagram.com/framersmethod/" target="_blank" rel="noreferrer">
                        <img src="/white logo insta.png" alt="The Framers' Method on Instagram" />
                    </a>
                    <a href="https://twitter.com/framersmethod" target="_blank" rel="noreferrer">
                        <img src="/white logo x.png" alt="The Framers' Method on Twitter" />
                    </a>
                    <a href="https://www.youtube.com/@framersmethod/featured" target="_blank" rel="noreferrer">
                        <img src="/white logo youtube.png" alt="The Framers' Method on YouTube" />
                    </a>
                    <a href="https://www.patreon.com/framersmethod" target="_blank" rel="noreferrer">
                        <img src="/white logo patreon.png" alt="The Framers' Method on Patreon" />
                    </a>
                    <a href="https://www.tiktok.com/@framersmethod" target="_blank" rel="noreferrer">
                        <img src="/white logo tiktok.png" alt="The Framers' Method on TikTok" />
                    </a>
                    <a href="https://a.co/d/0dimzJAr" target="_blank" rel="noreferrer">
                        <img src="white logo amazon.png" alt="On The Framers' Method Book - Amazon"/>
                    </a>
                </div>
            </div>
        </footer>
    );
};

export default Footer;