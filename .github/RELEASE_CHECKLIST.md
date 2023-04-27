# How to release a new Mosparo Bundle version

1.  Commit and push the previous change.
2.  Create the tag and sign it. Example: `git tag -s v1.12.6` (the tag version is
    always prefixed with `v`, but remove it for the tag comment: `1.12.6`).
3.  Push the tag to GitHub. Example: `git push origin v1.12.6`
4.  Prepare the changelog of the new version with the custom `changelog` Git
    command. Example: `git changelog v1.12.5` (the version passed to the command
    is the previous version used as a reference to list the changes).
5.  Go to https://github.com/arnaud-ritti/mosparo-bundle/releases and click
    on `Draft a new release`. Select the tag pushed before and paste the
    changelog contents.

## Resources

The custom `changelog` Git command used to generate the version changelog can
be defined as a global Git alias:

    $ git config --global alias.changelog "!f() { git log $1...$2 --pretty=format:'[%h] %s' --reverse; } ; f"
