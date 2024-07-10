import React from "react";
import "./sections.css";

export const SocialMediaBar = () => (
  <section className="socialmediabar">
      <a href="https://www.instagram.com/framersmethod/" target="_blank" rel="noreferrer"><img src="white logo insta.png" alt="The Framers' Method on Instagram"/></a> 
      <a href="https://twitter.com/framersmethod" target="_blank" rel="noreferrer"><img src="white logo x.png" alt="The Framers' Method on Twitter"/></a>
      <a href="https://www.youtube.com/@framersmethod/featured" target="_blank" rel="noreferrer"><img src="white logo youtube.png" alt="The Framers' Method on YouTube"/></a>
      <a href="https://www.patreon.com/framersmethod" target="_blank" rel="noreferrer"><img src="white logo patreon.png" alt="The Framers' Method on Patreon"/></a>
      <a href="https://www.tiktok.com/@framersmethod" target="_blank" rel="noreferrer"><img src="white logo tiktok.png" alt="The Framers' Method on TikTok"/></a>
      <a href="https://a.co/d/0dimzJAr" target="_blank" rel="noreferrer"><img src="white logo amazon.png" alt="On The Framers' Method Book - Amazon"/></a>
  </section>
);

export const LogoOnly = () => (
  <section className="logoonly">
  <div className="logoonly-img">
    <img src="framers method tshirt.png" alt="The Framers' Method Logo" />
  </div>
  </section>
);

export const DividerWhite = () => (
  <section>
      <div className="dividerwhite"></div>
  </section>
);

export const Dividerf5f4f0 = () => (
  <section>
      <div className="dividerf5f4f0"></div>
  </section>
)

export const SpaceDivider = () => (
  <div className="spacedivider"></div>
);

export const HomeMainLogo = () => (
  <section className="homemainlogo">
    <div className="homemainlogo-img">
      <img src="framers method tshirt.png" alt="The Framers' Method Logo" />
    </div>
    <div className="homemainlogo-text">
      <div className="homemainlogo-text-h2">The American republic...</div>
      <div className="homemainlogo-text-p">...is under threat from populism and tyrannical-minded politicians. The Framers’ Method can restore political stability to government and the American people.</div>
    </div>
  </section>
);

export const HomeUnderstanding = () => (
  <section className="sectiontemplate">
    <div className="sectiontemplate-text">
        <div className="sectiontemplate-text-h2">Understanding the Problem</div>
        <div className="sectiontemplate-text-h1">American politics has become centralized around two political parties. </div>
        <div className="sectiontemplate-text-p">With our open primary system, tyrannically-mined candidates may take over one of the two major parties. They can then use populist rhetoric to influence and control the American people. More often than not, this rhetoric is a distraction from the voters’ interests.</div>
    </div>
    <div class="sectiontemplate-img">
        <img src="../ElectoralCollege2024 map.png" alt="The current Electoral College Map" />
    </div>
  </section>
);

export const HomeBringBackRepublic = () => (
  <section className="sectiontemplate" style={{ backgroundColor: '#f5f4f0' }}>
    <div className="sectiontemplate-text">
        <div className="sectiontemplate-text-h2">Bring back the Republic</div>
        <div className="sectiontemplate-text-h1">Decentralized elections will promote the constitutional ideas of the framers. </div>
        <div className="sectiontemplate-text-p">By eliminating the centralized and national election system and replacing it with local elections, the American people can defeat populism and tyranny. Democratic systems create demagogues, but their powers of populist rhetoric on the national stage are mitigated with local elections. </div>
    </div>
    <div class="sectiontemplate-img">
        <img src=".//electoral college hamilton method no background.png" alt="The Electoral College under the Hamilton Method" />
    </div>
  </section>
);

export const HomeDeliberation = () => (
  <section className="sectiontemplate" >
    <div className="sectiontemplate-text">
        <div className="sectiontemplate-text-h2">America needs Deliberation</div>
        <div className="sectiontemplate-text-h1">A process that uses deliberation will give America the type of president we need. </div>
        <div className="sectiontemplate-text-p">The democratic model for elections concentrates power on majority rule and suppresses the minority. The republican model creates a variety of interests throughout the several states and brings them together into a deliberative environment. Whereas democratic systems create demagogues, republican systems create a political situation where negotiation and compromise are the keys to success.</div>
    </div>
    <div class="sectiontemplate-img">
    <img src=".//convention white.png" alt="An Electors Convention" />
    </div>
  </section>
);

export const HomeVideo = () => (
  <section className="homevideo">
  <div className="homevideo-h2" style={{ color: 'black' }}>The Framers' Method will defeat populism and tyranny.</div>
  <div className="homevideo-container">
      <iframe src="https://www.youtube.com/embed/_6jD8nm8QvM?si=bPEGAg82lViYl99n" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
  </div>
  </section>
);

export const ContributeLogo = () => (
  <section className="homemainlogo">
    <div className="homemainlogo-img">
      <img src="framers method tshirt.png" alt="The Framers' Method Logo" />
    </div>
    <div className="homemainlogo-text">
      <div className="homemainlogo-text-h2">Restore the American Republic!</div>
      <div className="homemainlogo-text-p" >Contribute to the The Framers’ Method. Help defeat Populism and Tyranny. Join the Framers' Method and help us...</div>
    </div>
  </section>
);

export const ContributePatreon = () => (
  <section className="contribute">
    <div className="contribute-text">
      <div className="contribute-text-h1">Support us on Patreon</div>
      <div className="contribute-text-p">The best way to support the Framers' Method is through a subscription on Patreon.</div>
      <a className="contribute-button" href="https://www.patreon.com/framersmethod" target="_blank" rel="noreferrer">
      <button class="btn-tshirt">Support Here</button>
      </a>
    </div>
    <div class="contribute-img">
        <img src="../patreon img.png" alt="The Framers' Method on Patreon" />
    </div>
  </section>
);

export const ContributeShortSleeve = () => (
  <section className="contribute">
    <div className="contribute-text">
      <div className="contribute-text-h1">The Framers' Method Short Sleeve Shirt</div>
      <a href="https://framersmethod.printify.me/product/3613867/the-framers-method-unisex-jersey-short-sleeve-tee" target="_blank" rel="noreferrer"><button className="btn-tshirt">Buy Here</button></a>
    </div>
    <div class="contribute-img">
      <a href="https://framersmethod.printify.me/product/3613867/the-framers-method-unisex-jersey-short-sleeve-tee" target="_blank" rel="noreferrer">
      <img src="../framers short sleeve.png" alt="The Framers' Method - Short Sleeve" />
      </a>
    </div>
  </section>
);

export const ContributeVneck = () => (
  <section className="contribute">
    <div className="contribute-text">
      <div className="contribute-text-h1">The Framers' Method V-Neck Shirt</div>
      <a href="https://framersmethod.printify.me/product/7705494/the-framers-method-unisex-jersey-short-sleeve-v-neck-tee" target="_blank" rel="noreferrer"><button class="btn-tshirt">Buy Here</button></a>
    </div>
    <div class="contribute-img">
      <a href="https://framersmethod.printify.me/product/7705494/the-framers-method-unisex-jersey-short-sleeve-v-neck-tee" target="_blank" rel="noreferrer"><img src="../framers vneck.png" alt="The Framers' Method - Vneck" /></a>
    </div>
  </section>
);

export const ContributeDeepState = () => (
  <section className="contribute">
    <div className="contribute-text">
      <div className="contribute-text-h1">Deep State Warriors Short Sleeve</div>
      <a href="https://framersmethod.printify.me/product/7706169/deep-state-warriors-unisex-jersey-short-sleeve-tee" target="_blank" rel="noreferrer"><button class="btn-tshirt">Buy Here</button></a>
    </div>
    <div class="contribute-img">
    <a href="https://framersmethod.printify.me/product/7706169/deep-state-warriors-unisex-jersey-short-sleeve-tee" target="_blank" rel="noreferrer"><img src="../warriors tshirt.png" alt="The Deep State Warrios - Short Sleeve" /></a>
    </div>
  </section>
);

export const ElectorsText = () => (
  <section className="sectiontemplate" style={{backgroundColor: "white"}}>
    <div className="sectiontemplate-text">
      <div className="sectiontemplate-text-h1">Deliberation will give America the president it needs for the future. </div>
      <div className="sectiontemplate-text-p">After the several states choose their electors, an electors convention will choose the next president. In this environment, populism and tyranny are impossible. To become the next president one of the electors will need the skills of negotiation and compromise. </div>
    </div>
    <div class="sectiontemplate-img">
      <img src=".//convention white.png" alt="An Electors Convention" />
    </div>
  </section>
);

export const ElectorsKeypoints = () => (
  <section className="electorskeypoints" >
    <div className="electorskeypoints-text" >
      <div className="sectiontemplate-text-h1">Key Points of an Electors Convention:</div>
      <div className="sectiontemplate-text-p">• Creates a deliberative environment</div>
      <div className="sectiontemplate-text-p">• Compromise and negotiation are required</div>
      <div className="sectiontemplate-text-p">• Populism and tyranny are ineffective</div>
      <div className="sectiontemplate-text-p">• Money is not required to hold an electors convention</div>
      <div className="sectiontemplate-text-p">• Foreign intelligence services cannot influence</div>
      <div className="sectiontemplate-text-p">• Mass media and social media cannot influence</div>
    </div>
  </section>
);

export const HamiltonHowitworks = () => (
  <section className="sectiontemplate">
    <div className="sectiontemplate-text">
      <div className="sectiontemplate-text-h2">How it works</div>
      <div className="sectiontemplate-text-h1">A decentralized election system will prevent national populist rhetoric.</div>
      <div className="sectiontemplate-text-p">With thousands of possible electors, political influence is dispersed throughout the country. No elector candidate will wield concentrated power on the national stage. Money in politics, media influence, and foreign intelligence services will have little influence on the electoral process.</div>
    </div>
    <div class="sectiontemplate-img">
      <img src="../hamilton method img 3.png" alt="The Hamilton Method"/></div>
  </section>
);

export const HamiltonThehamiltonmethod = () => (
  <section className="sectiontemplate">
    <div className="sectiontemplate-text">
      <div className="sectiontemplate-text-h2">The Hamilton Method</div>
      <div className="sectiontemplate-text-h1">A decentralized election system will prevent national populist rhetoric.</div>
      <div className="sectiontemplate-text-p">With thousands of possible electors, political influence is dispersed throughout the country. No elector candidate will wield concentrated power on the national stage. Money in politics, media influence, and foreign intelligence services will have little influence on the electoral process.</div>
    </div>
    <div class="sectiontemplate-img">
      <img src="../electoral college hamilton method no background.png" alt="The Hamilton Method"/>
    </div>
  </section>
);

export const HamiltonKeypoints = () => (
  <section className="sectiontemplate">
    <div className="sectiontemplate-text">
      <div className="sectiontemplate-text-h1">Key Points of the Hamilton Method:</div>
      <div className="sectiontemplate-text-p">• Elections are local within each state</div>
      <div className="sectiontemplate-text-p">• The national election is eliminated</div>
      <div className="sectiontemplate-text-p">• Populism and tyranny are ineffective</div>
      <div className="sectiontemplate-text-p">• Money is still used for campaigning, but will be decentralized</div>
      <div className="sectiontemplate-text-p">• Thousands of potential electors prevent influence by foreign intelligence services as well as traditional media and social media</div>
    </div>
  </section>
);

export const Democracy = () => (
  <section className="sectiontemplate">
    <div className="sectiontemplate-text">
      <div className="sectiontemplate-text-h1">Democracy</div>
      <div className="sectiontemplate-text-p">Derived from Greek, with dēmos meaning “the people” and -kratiā meaning “rule.” Also referred to as direct democracy.</div>
    </div>
    <div class="sectiontemplate-img">
      <img src="../greek democracy.png" alt="Greek Democracy"/></div>
  </section>
);

export const Republic = () => (
  <section className="sectiontemplate">
    <div className="sectiontemplate-text">
      <div className="sectiontemplate-text-h1">Republic</div>
      <div className="sectiontemplate-text-p">Derived from Latin, with rēs meaning “thing” and pūblicus meaning “of the people.” The Republic is a system where the people create a small body or several small bodies to make the rules for society. Republics are often referred to as representative democracy or constitutional government.</div>
    </div>
    <div class="sectiontemplate-img">
    <img src="../roman republic.png" alt="Roman republic"/>
    </div>
  </section>
);

export const DemocracyVsRepublic = () => (
  <section className="sectiontemplate">
    <div className="sectiontemplate-text">
      <div className="sectiontemplate-text-h1">Republic vs. Democracy</div>
      <div className="sectiontemplate-text-p">A republic is a government where the voters create a small body to make the rules, while in a democratic government the voters directly make the rules.</div>
    </div>
    <div class="sectiontemplate-img">
      <img src="../rep vs dem.png" alt="The difference between a Republic and a Democracy"/>
    </div>
  </section>
);