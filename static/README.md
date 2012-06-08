# Static - a module for providing SilverStripe content statically

## Introduction

SilverStripe CMS is awesome for providing content management to content editors, managers and other semi-technical individuals. But sometimes
as a developer you want to throw a SilverStripe site up, and you'd like all the content to live in the GIT repository with your code.

This module replaces CMS, providing a different page type that gets it's data from yml files in the code repository instead of
from a database

## Usage

* Install module (and don't install cms - the two currently don't interact well)

* Create a directory, mysite/content (or change StaticTree::$root to point to where-ever your content is)

* Write Pages and Page_Controller types as normal, but inherit off StaticTree and StaticController instead of SiteTree and ContentController

* Instead of creating content through the CMS, put it in yaml files. Your tree structure comes from the nested directory structure
   underneath mysite/content. index.yml is a special case - it's used for the directories themselves. Content is by default MarkDown
   based

## Gotchas

Pages are no longer DataObjects, so you can't currently write to them programatically. You also can't use $db - use $fields instead.
See StaticModel for details
