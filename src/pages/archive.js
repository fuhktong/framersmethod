import React from 'react';
import { DividerWhite, LogoOnly } from '../sections.js';
import { Link } from 'react-router-dom';
import '../sections.js';
import { Helmet } from 'react-helmet-async';
import { HelmetProvider } from 'react-helmet-async';

const helmetContext = {};

const Archive = () => {
  return (
    <HelmetProvider context={helmetContext}>
    <section>
      <Helmet>
        <title>The Framers' Method - Archives</title>
        <meta name="description" content="Explore the Framers' Method archived source materials and videos related to reforming the Electoral College and other political institutions." />
        <meta name="keywords" content="electoral college, presidential elections, american politics, electors, electoral votes, president, constitution" />
        <meta name="author" content="Dustin Taylor" />
        <meta name="robots" content="index, follow" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="English" />

        <meta property="og:title" content="The Framers' Method - Archives" />
        <meta property="og:description" content="Explore the Framers' Method archived source materials and videos related to reforming the Electoral College and other political institutions." />
        <meta property="og:image" content="https://www.framersmethod.com/framers%20method%20tshirt.png" />
        <meta property="og:url" content="https://www.framersmethod.com/archives" />
        <meta property="og:type" content="website" />

        <meta name="twitter:title" content="The Framers' Method - Archives" />
        <meta name="twitter:description" content="Explore our comprehensive archive of source materials and videos related to reforming the Electoral College and other political institutions." />
        <meta name="twitter:image" content="https://www.framersmethod.com/framers%20method%20tshirt.png" />
        <meta name="twitter:card" content="summary_large_image" />
      </Helmet>
      <LogoOnly />
      <DividerWhite />
        <div className="archive">Archive</div>
      <DividerWhite />
        <div className="archive-dates">
        <div className="archive-months">May 2024</div>
        <Link className="archive-title" to="/video007">Video - The Framers' Method 7/9: The Hamilton Effect</Link>
        <div className="archive-months">December 2023</div>
        <Link className="archive-title" to="/video006">Video - The Framers’ Method 6/9: The Hamilton Method</Link>
        <div className="archive-months">November 2023</div>
        <Link className="archive-title" to="/video005">Video - The Framers’ Method 5/9: Tyranny of the Candidates</Link>
        <Link className="archive-title" to="/video004">Video - The Framers’ Method 4/9: The Framers' Failure</Link>
        <Link className="archive-title" to="/video003">Video - The Framers’ Method 3/9: Origin Story: The Second Great Compromise</Link>
        <Link className="archive-title" to="/video002">Video - The Framers' Method 2/9: Origin Story: A Battle of Ideas</Link>
        <Link className="archive-title" to="/video001">Video: The Framers’ Method 1/9: An Introduction to the Framers’ Method</Link>
      </div>
      <DividerWhite />
    </section>
    </HelmetProvider>
  );
};

export default Archive;