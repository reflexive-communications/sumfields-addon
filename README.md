# sumfields-addon

[![CI](https://github.com/reflexive-communications/sumfields-addon/actions/workflows/main.yml/badge.svg)](https://github.com/reflexive-communications/sumfields-addon/actions/workflows/main.yml)

This extension adds extra summary fields provided by the [Summary Fields](https://github.com/progressivetech/net.ourpowerbase.sumfields)
extension.

Also changes functionality, fields are always regenerated with the `SumFields.Gendata` API call.
Originally fields are only regenerated when the fiscal year has changed since the last run.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.3+
* CiviCRM (5.37 might work below - not tested)
* net.ourpowerbase.sumfields

## Installation

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/reflexive-communications/sumfields-addon
cv en sumfields_addon
```

## Getting Started

On the **Administer >> Customize Data and Screens >> Summary Fields** admin page, you can find the extra fields.

**Extra fields**

- Largest contribution in the last 12 months
- Number of contributions in the last month
- Number of contributions in the last 45 days
- Number of contributions in the last 62 days
- Number of contributions in the last 110 days
- Number of contributions in the last 3 months
- Number of contributions in the last 6 months
- Number of contributions in the last 12 months
- Number of contributions in the last 2 years
