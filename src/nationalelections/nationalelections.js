import React from "react";
import { SocialMediaBar } from "../socialmediabar";
import { Helmet } from "react-helmet-async";
import PageTransition from "../pagetransition.js";
import "./nationalelections.css";

const NationalElections = () => {
  return (
    <PageTransition>
      <section>
        <Helmet>
          {/* Essential/Basic Meta Tags */}
          <title>
            The Framers&#39; Method: Electoral College & Hamilton Method
            Explained - National Elections
          </title>
          <meta charset="utf-8" />
          <meta
            name="description"
            content="National elections, the Electoral College and any proposed popular vote, are destroying America."
          />
          <meta
            name="keywords"
            content="caucus, general caucus, elections, american politics, electors, republic, president, constitution, democracy, electors convention"
          />
          <meta name="author" content="Dustin Taylor" />
          <meta name="language" content="English" />
          <meta name="robots" content="index, follow" />
          <meta http-equiv="Content-Type" content="text/html"></meta>
          {/* Open Graph Tags */}
          <meta property="og:type" content="website" />
          <meta property="og:url" content="https://www.framersmethod.com/" />
          <meta property="og:site_name" content="The Framers&#39; Method" />
          <meta
            property="og:title"
            content="The Framers&#39; Method - National Elections"
          />
          <meta
            property="og:description"
            content="National elections, the Electoral College and any proposed popular vote, are destroying America."
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
          <meta
            name="twitter:title"
            content="The Framers&#39; Method - National Elections"
          />
          <meta
            name="twitter:description"
            content="National elections, the Electoral College and any proposed popular vote, are destroying America."
          />
          <meta
            name="twitter:image"
            content="https://www.framersmethod.com/framersmethodlogo-withbackground.png"
          />
        </Helmet>
        <div class="national-logo">
          <img src="../nationalelectionslogo.png" alt="National Elections" />
        </div>
        <SocialMediaBar />
        {/* INTRO WITH DIAGRAM */}
        <section
          className="national-template"
          style={{ backgroundColor: "white" }}
        >
          <div className="national-template-text">
            <h1>National Elections</h1>
            <h2>
              Parties, Money, and the Media have a strangle hold presidential
              elections.
            </h2>
            <p>
              Concentrated interests undermine the will of the people and pursue
              interests against the public good. The people have become divided
              while the oligarchs exploit the system and tyrants destabilize the
              country with populist rhetoric. Parties, money, and the media are
              the base of infleunce in our elections.
            </p>
          </div>
          <div class="national-template-img">
            <img
              src="./nationalelectionsdiagram.png"
              alt="National Elections Diagram"
            />
          </div>
        </section>
        {/* POLITICAL PARTIES */}
        <section
          className="national-template"
          style={{ backgroundColor: "#f5f4f0" }}
        >
          <div className="national-template-text">
            <h1>Political Parties</h1>
            <h2>The two parties do not give voters a real choice.</h2>
            <p>
              In the nominating primaries, very few voters turn out to vote and
              the party’s nominee is determined after only a few contests.
              Voters in primaries tend to be more ideologically extreme,
              equating to more extreme nominees. After the primaries, parties
              present their nominees to the general electorate. With the
              two-party system, there are only two options. There isn’t a real
              choice for voters.
            </p>
          </div>
          <div class="national-template-img">
            <img
              src="./donkeyvselephant.png"
              alt="Democrats Versus Republicans"
            />
          </div>
        </section>
        {/* MONEY IN POLITICS */}
        <section
          className="national-template"
          style={{ backgroundColor: "white" }}
        >
          <div className="national-template-text">
            <h1>Money in Politics</h1>
            <h2>
              The amount of money in politics delegitimizes the electoral
              process.
            </h2>
            <p>
              *Over 1.8 billion dollars was raised in the 2024 presidential
              election and over 15.9 billion dollars was spent on all federal
              elections. The outsized role of money undermines the democratic
              principle of equal representation, as wealthy donors and special
              interest groups gain disproportionate access to candidates and
              policymakers. This leads to policies that favor a few over the
              many and erodes public trust in government institutions. Moreover,
              the perpetual need for fundraising distracts elected officials
              from their duties, further entrenching inequality and prioritizing
              monetary interests over the public good.
            </p>
            <p>*opensecrets.org</p>
          </div>
          <div class="national-template-img">
            <img src="./moneyinpolitics.png" alt="Money in Politics" />
          </div>
        </section>
        {/* A DESTRUCTIVE MEDIA */}
        <section
          className="national-template"
          style={{ backgroundColor: "#f5f4f0" }}
        >
          <div className="national-template-text">
            <h1>Media Influence is Destroying America</h1>
            <h2>
              Legacy Media and Social Media have perpetuated fear and turned
              Americans against each another.
            </h2>
            <p>
              The media plays the most destructive role as various outlets play
              on fear for ratings. Different channels take ideological sides
              resulting in the promotion of their preferred candidates and the
              demonization of the opposition candidate. Topics in the media
              rarely discuss issues of the public good and mostly focus on the
              horse-race between the candidates.
            </p>
            <p>
              Social media has become a cess pool of misinformation and
              conspiracy theories. Most often, no one knows the source of
              disinformation campaigns, whether they be foreign intelligence
              operations or disturbed individuals.
            </p>
          </div>
          <div class="national-template-img">
            <img
              src="./socialmediafear.png"
              alt="Media Influence Causes Fear"
            />
          </div>
        </section>
        <section className="national-template">
          <div className="national-template-text">
            <h2 style={{ width: "70%", textAlign: "center", margin: "0 auto" }}>
              National elections concentrate the political power of our country
              into the hands of two people. And money and the media have an
              oversized influence on these two people. The framers feared
              national elections and so should we.{" "}
            </h2>
          </div>
        </section>
      </section>
    </PageTransition>
  );
};

export default NationalElections;
