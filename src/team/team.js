import React from "react";
import { SocialMediaBar } from "../socialmediabar";
import { Helmet } from "react-helmet-async";
import PageTransition from "../pagetransition.js";
import "./team.css";

const Team = () => {
  return (
    <PageTransition>
      <section>
        <Helmet>
          {/* Essential/Basic Meta Tags */}
          <title>
            The Framers' Method: Electoral College & Hamilton Method Explained -
            Home
          </title>
          <meta charset="utf-8" />
          <meta
            name="description"
            content="The Framers' Method can defeat populism and tyranny by using the Electoral College and the Hamilton Method."
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
          <meta property="og:site_name" content="The Framers' Method" />
          <meta property="og:title" content="The Framers' Method - Home" />
          <meta
            property="og:description"
            content="The Framers' Method can defeat populism and tyranny by using the Electoral College and the Hamilton Method."
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
          <meta name="twitter:title" content="The Framers' Method - Home" />
          <meta
            name="twitter:description"
            content="The Framers' Method can defeat populism and tyranny by using the Electoral College and the Hamilton Method."
          />
          <meta
            name="twitter:image"
            content="https://www.framersmethod.com/framersmethodlogo-withbackground.png"
          />
        </Helmet>
        <div class="team-logo">
          <img src="../framersmethodteam.png" alt="The Framers' Method Team" />
        </div>
        <SocialMediaBar />
        <section className="team-template" style={{ backgroundColor: "white" }}>
          <div class="team-template-img">
            <img src="./dustintaylorphoto.png" alt="Dustin Taylor" />
            <h1>Dustin Taylor</h1>
            <h2>Founder / Executive Director</h2>
          </div>
          <div className="team-template-text">
            <p>
              Dustin Taylor is a political scientist and the founder of the
              Framers’ Method. Driven by a reform-minded passion for American
              politics, he has published a thought-provoking book on the
              Electoral College, advocating for a deliberative electors'
              convention to select the president.
            </p>
            <p>
              As a field staffer on five political campaigns, Dustin gained
              invaluable firsthand experience in strategy, communication, and
              grassroots organizing. He holds a master’s degree in Government
              from Johns Hopkins University and a bachelor’s degree from The
              University of New Mexico.
            </p>
            <p>
              Before pursuing his academic and professional career, Dustin
              proudly served in the U.S. Navy as a Cryptologic Technician, where
              he honed his discipline and strategic thinking. Dustin is from
              Albuquerque, New Mexico.
            </p>
          </div>
        </section>
        <section className="team-template" style={{ backgroundColor: "white" }}>
          <div class="team-template-img">
            <img src="./samlopezphoto.png" alt="Sam Lopez" />
            <h1>Sam Lopez</h1>
            <h2>Deputy Director</h2>
          </div>
          <div className="team-template-text">
            <p>
              As Deputy Director of The Framers’ Method, he leads strategic
              initiatives to strengthen democratic institutions and implement
              practical solutions for election reform.
            </p>
            <p>
              Sam's experience in business operations management has been
              leveraged by international development NGOs, highly regulated
              financial firms, startup businesses, and entertainment venues for
              over fifteen years.
            </p>
            <p>
              His work focuses on advancing The General Caucus, The Hamilton
              Method, an Electors' Convention, and increasing voter
              accessibility across state systems. Through his work and civic
              activism Sam has dedicated his life to fighting the systemic
              forces creating inequality in America's economic and political
              systems. Sam is from Des Moines, Iowa.
            </p>
          </div>
        </section>
      </section>
    </PageTransition>
  );
};

export default Team;
