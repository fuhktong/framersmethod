import React from 'react';
import { Link } from 'react-router-dom';
import '../sections.css';
import { Helmet } from 'react-helmet-async';

export const News = () => (
  <section className='news-all'>
    <Helmet>
      <title>The Framers' Method - News</title>
      <meta name="description" content="Get the latest insite on the Electoral College and the Hamilton as it realtes to current events." />
      <meta name="keywords" content="electoral college, presidential elections, american politics, electors, electoral votes, president, constitution" />
      <meta name="author" content="Dustin Taylor" />
      <meta name="robots" content="index, follow"></meta>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>
      <meta name="language" content="English"></meta>
      <meta property="og:title" content="The Framers' Method - News" />
      <meta property="og:description" content="Get the latest insite on the Electoral College and the Hamilton as it realtes to current events." />
      <meta property="og:image" content="https://www.framersmethod.com/framers%20method%20tshirt.png" />
      <meta property="og:url" content="https://www.framersmethod.com/news" />
      <meta property="og:type" content="website" />
      <meta name="twitter:title" content="The Framers' Method - News" />
      <meta name="twitter:description" content="Get the latest insite on the Electoral College and the Hamilton as it realtes to current events." />
      <meta name="twitter:image" content="https://www.framersmethod.com/framers%20method%20tshirt.png" />
      <meta name="twitter:card" content="summary_large_image" />
    </Helmet>

    <section className="news8">
      <section className="sectiontemplate" style={{paddingTop: "150px"}}>
        <div className="sectiontemplate-text">
          <div className="sectiontemplate-text-h1">If you want Joe Biden to step down, you need a party to do it </div>
          <div className="sectiontemplate-text-p">11 July 2024</div>
        </div>
        <div className="sectiontemplate-img">
          <Link to="/joe-biden-stepping-down">
          <img src="/joe biden stepping down.jpeg" alt="Joe Biden stepping down - courtesy Gerald Herbert - AP" />
          </Link>
        </div>
      </section>
    </section>

    <section className="news7">
      <section className="sectiontemplate" style={{paddingTop: "150px"}}>
        <div className="sectiontemplate-text">
          <div className="sectiontemplate-text-h1">Video - The Framers' Method 7/9: The Hamilton Effect </div>
          <div className="sectiontemplate-text-p">12 May 2023</div>
        </div>
        <div className="sectiontemplate-img">
          <Link to="/video007">
          <img src="newsthumbnail007.jpg" alt="The Framers' Method Logo" />
          </Link>
        </div>
      </section>
    </section>

    <section className="news6">
      <section className="sectiontemplate" style={{paddingTop: "150px"}}>
        <div className="sectiontemplate-text">
          <div className="sectiontemplate-text-h1">Video - The Framers’ Method 6/9: The Hamilton Method </div>
          <div className="sectiontemplate-text-p">07 Dec 2023</div>
        </div>
        <div className="sectiontemplate-img">
          <Link to="/video006">
          <img src="newsthumbnail006.jpg" alt="The Framers' Method Logo" />
          </Link>
        </div>
      </section>
    </section>

    <section className="news5">
      <section className="sectiontemplate" style={{paddingTop: "150px"}}>
        <div className="sectiontemplate-text">
          <div className="sectiontemplate-text-h1">Video - The Framers’ Method 5/9: Tyranny of the Candidates </div>
          <div className="sectiontemplate-text-p">25 Nov 2023</div>
        </div>
        <div className="sectiontemplate-img">
          <Link to="/video005">
          <img src="newsthumbnail005.jpg" alt="The Framers' Method Logo" />
          </Link>
        </div>
      </section>
    </section>

    <section className="news4">
      <section className="sectiontemplate" style={{paddingTop: "150px"}}>
        <div className="sectiontemplate-text">
          <div className="sectiontemplate-text-h1">Video - The Framers’ Method 4/9: The Framers' Failure </div>
          <div className="sectiontemplate-text-p">13 Nov 2023</div>
        </div>
        <div className="sectiontemplate-img">
          <Link to="/video004">
          <img src="newsthumbnail004.jpg" alt="The Framers' Method Logo" />
          </Link>
        </div>
      </section>
    </section>

    <section className="news3">
      <section className="sectiontemplate" style={{paddingTop: "150px"}}>
        <div className="sectiontemplate-text">
          <div className="sectiontemplate-text-h1">Video - The Framers’ Method 3/9: Origin Story: The Second Great Compromise </div>
          <div className="sectiontemplate-text-p">06 Nov 2023</div>
        </div>
        <div className="sectiontemplate-img">
          <Link to="/video003">
          <img src="newsthumbnail003.jpg" alt="The Framers' Method Logo" />
          </Link>
        </div>
      </section>
    </section>

    <section className="news2">
      <section className="sectiontemplate" style={{paddingTop: "150px"}}>
        <div className="sectiontemplate-text">
          <div className="sectiontemplate-text-h1">Video - The Framers' Method 2/9: Origin Story: A Battle of Ideas </div>
          <div className="sectiontemplate-text-p">06 Nov 2023</div>
        </div>
        <div className="sectiontemplate-img">
          <Link to="/video002">
          <img src="newsthumbnail002.jpg" alt="The Framers' Method Logo" />
          </Link>
        </div>
      </section>
    </section>

    <section className='news1'>
      <section className="sectiontemplate" style={{paddingTop: "150px"}}>
        <div className="sectiontemplate-text">
          <div className="sectiontemplate-text-h1">Video - The Framers’ Method 1/9: An Introduction to the Framers’ Method </div>
          <div className="sectiontemplate-text-p">06 Nov 2023</div>
        </div>
        <div className="sectiontemplate-img">
          <Link to="/video001">
          <img src="newsthumbnail001.png" alt="The Framers' Method Logo" />
          </Link>
        </div>
      </section>
    </section>
  </section>
);

export default News;