import React from "react";
import { Link } from "react-router-dom";
import { SocialMediaBar } from "../socialmediabar.js";
import { Helmet } from "react-helmet-async";
import PageTransition from "../pagetransition.js";
import "./home.css";

const Home = () => {
  return (
    <PageTransition>
      <section>
        <Helmet>
          {/* Essential/Basic Meta Tags */}
          <title>
            The Framers&#39; Method: Electoral College & Hamilton Method
            Explained - Home
          </title>
          <meta charset="utf-8" />
          <meta
            name="description"
            content="The Framers&#39; Method can defeat populism and tyranny by using the Electoral College and the Hamilton Method."
          />
          <meta
            name="keywords"
            content="electoral college, presidential elections, american politics, electors, republic, president, constitution, democracy, electors convention"
          />
          <meta name="author" content="Dustin Taylor" />
          <meta name="language" content="English" />
          <meta name="robots" content="index, follow" />
          <meta http-equiv="Content-Type" content="text/html"></meta>
          {/* Open Graph Tags */}
          <meta property="og:type" content="website" />
          <meta property="og:url" content="https://www.framersmethod.com/" />
          <meta property="og:site_name" content="The Framers&#39; Method" />
          <meta property="og:title" content="The Framers&#39; Method - Home" />
          <meta
            property="og:description"
            content="The Framers&#39; Method can defeat populism and tyranny by using the Electoral College and the Hamilton Method."
          />
          <meta
            property="og:image"
            content="https://www.framersmethod.com/framersmethodlogo-withbackground.png"
          />
          <meta property="og:image:width" content="1200" />
          <meta property="og:image:height" content="630" />
          {/* Twitter Tags */}
          <meta name="twitter:card" content="summary_large_image" />
          <meta name="twitter:site" content="@framersmethod" />
          <meta name="twitter:title" content="The Framers&#39; Method - Home" />
          <meta
            name="twitter:description"
            content="The Framers&#39; Method can defeat populism and tyranny by using the Electoral College and the Hamilton Method."
          />
          <meta
            name="twitter:image"
            content="https://www.framersmethod.com/framersmethodlogo-withbackground.png"
          />
        </Helmet>
        <div className="home-logo">
          <img src="./framersmethod.png" alt="The Framers' Method" />
        </div>
        <div className="home-text">
          <h2>The American republic...</h2>
          <p>
            ...is under threat from populism and tyrannical-minded politicians.
            The Framers' Method can restore political stability to the
            government and the American people. With tools like the General
            Caucus, the Hamilton Method, and the Electors' Convention, we can
            restore the sovereignty of the American people.
          </p>
        </div>
        <SocialMediaBar />
        <section className="home-template">
          <div className="home-template-text">
            <div className="home-template-text">
              <h2>Understanding the Problem</h2>
            </div>
            <div className="home-template-text">
              <h1>
                American politics has become centralized around two political
                parties.
              </h1>
            </div>
            <div className="home-template-text">
              <p>
                With our open primary system, tyrannically-mined candidates may
                take over one of the two major parties. They can then use
                populist rhetoric to influence and control the American people.
                More often than not, this rhetoric is a distraction from the
                public good.
              </p>
            </div>
          </div>
          <div className="home-template-img">
            <img
              src="../electoralcollege2024map.png"
              alt="The Current Electoral College Map"
            />
          </div>
        </section>
        <section
          className="home-template"
          style={{ backgroundColor: "#f5f4f0" }}
        >
          <div className="home-template-text">
            <div className="home-template-text">
              <h2>Bring back the Republic</h2>
            </div>
            <div className="home-template-text">
              <h1>
                Decentralized elections will promote the constitutional ideas of
                the framers.
              </h1>
            </div>
            <div className="home-template-text">
              <p>
                By eliminating the centralized national election system and
                replacing it with local elections, the American people can
                defeat populism and tyranny. Democratic systems often produce
                demagogues, but their powers of populist rhetoric on the
                national stage are diminished when local elections are used.
              </p>
            </div>
          </div>
          <div className="home-template-img">
            <img
              src="./hamiltonmethodnobackground.png"
              alt="The Electoral College Under the Hamilton Method"
            />
          </div>
        </section>
        <section className="home-template">
          <div className="home-template-text">
            <div className="home-template-text">
              <h2>America needs Deliberation</h2>
            </div>
            <div className="home-template-text">
              <h1>
                A process that uses deliberation will give America the type of
                president we need.
              </h1>
            </div>
            <div className="home-template-text">
              <p>
                The <Link to="/democracy-vs-republic">democratic model</Link>{" "}
                for elections concentrates power on majority rule and suppresses
                the minority. The{" "}
                <Link to="/democracy-vs-republic">republican model</Link>{" "}
                creates a variety of interests throughout the several states and
                brings them together into a deliberative environment. Whereas
                democratic systems create demagogues, republican systems create
                a political situation where negotiation and compromise are the
                keys to success.
              </p>
            </div>
          </div>
          <div className="home-template-img">
            <img src=".//convention.png" alt="An Electors' Convention" />
          </div>
        </section>
        <section
          className="homevideo-title"
          style={{ color: "black", backgroundColor: "#f5f4f0" }}
        >
          <h2>
            With tools like the General Caucus, the Hamilton Method, and an
            Electors' Convention, the Framers' Method will defeat populism and
            tyranny.
          </h2>
        </section>
        <div className="homevideo-wrapper">
          <seciton className="homevideo-container">
            <iframe
              src="https://www.youtube.com/embed/_6jD8nm8QvM?si=bPEGAg82lViYl99n"
              title="YouTube video player"
              frameBorder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              referrerPolicy="strict-origin-when-cross-origin"
              allowFullScreen
            ></iframe>
          </seciton>
        </div>
      </section>
    </PageTransition>
  );
};

export default Home;
