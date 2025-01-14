## Create polls and voting for Contao Open Source CMS ##

![](https://img.shields.io/packagist/v/toteph42/voting.svg)
![](https://img.shields.io/packagist/l/toteph42/voting.svg)
![](https://img.shields.io/packagist/dt/toteph42/voting.svg)

This bundle is based on [contao-polls](https://github.com/codefog/contao-polls). It is sligthly modified,
a couple on bug fixes were applied and the whole bundle is made compatible with the developer rules defined for
[Contao 5.3 LTS](https://contao.org/de/).

## Installation

#### Via composer
```
composer require toteph42/voting
```

#### Via contao-manager
```
Search for voing bundle and add it to your extensions.
```

After installing the contao-member-extension-bundle, you need to run a **contao install**.

## Setup a voting request

In member section of Contao you may specify two additional configuration parameters:

<img src="images/voting9.png" width="80%" height="60%" />


- The first **Voting shares** has a default set to 1. This means on each voting this user has a voting share of 1. 
You may modify this parameter to e.g. 20.7 (or any other number).
- The second parameter is the **Voting alias**. This defaults to nothing. In this case the users name is taken. If you want to add multiple user 
which will take part in a voting and you want them to share the **Voting share** then you may specify a **Voting alias** e.g. `Group01`.
- These option are usefull, if you have e.g. a homeowners association, where each homeowner has a different voting share 
or you have an appartment ownned by more than one owner. 

To setup a new vote request go to the `Voting` (in `CONTENT` tab)

<img src="images/voting1.png" />


Then you can create a new record using the `New voting` button. The voting configuration form has many 
options, but they are prefilled with the ready-to-use values.

<img src="images/voting2.png" width="60%" height="50%" />

Next important thing is the behavior configuration. Here you have to set how the vooting behaves after user 
has or has not voted. The most optimal behavior has been set as the default, but you can adjust it to your needs anytime.

<img src="images/voting3.png" width="80%" height="70%" />

Once you are done with the voting configuration, you have to create the voting options. Each option has the 
percentage bar displaying the current amount of the voting, if you have not sepcified a `Max. number of votes`. 
You can also view and manage the voting using the `voting` button in every row.

<img src="images/voting5.png" width="80%" height="70%" />

## Publish on the website

When the voting is ready, you can put it on the website as content element. You can either choose the voting manually or let the script find the most current one (based on voting settings).

<img src="images/voting5.png" width="80%" height="70%" />

On your web sute it will look like

<img src="images/voting8.png" width="50%" height="40%" />

On the web site you (and in back end) you will see the results (the provided `bundles/voting/style/voting.css` is
base on the [ODD Theme](https://contao-themes.net/theme-detail/odd.html). If you want to customize your `CSS` setting please create a new file `_custom.ss` in same directory.

<img src="images/voting6.png" width="50%" height="40%" />

If you have specifies `Max. number of votes` then you will see a sligthly different screen.

<img src="images/voting7.png" width="50%" height="40%" />

## Reset the poll

After all tests you can easily reset all votings using the `Reset voting` button at the top of the page:

Please enjoy!

If you enjoy my software, I would be happy to receive a donation.

<a href="https://www.paypal.com/donate/?hosted_button_id=DS6VK49NAFHEQ" target="_blank" rel="noopener">
  <img src="https://www.paypalobjects.com/en_US/DK/i/btn/btn_donateCC_LG.gif" alt="Donate with PayPal"/>
</a>

