# AI Log

> Fill this in with YOUR real interactions. The brief needs 5–10 entries, including at least 2 where the AI was wrong.
> Do not copy the examples below as if they happened to you. Delete them and write what actually happened.

## Entry template
**Prompt:** what you asked
**AI output:** what it gave you
**Decision:** accepted / changed / rejected, and why
**If wrong:** how you noticed, and how you fixed it

## Real things to look for while testing (candidates for "AI was wrong")
- Check that the first slideshow load does not replay the whole backlog as "Just in".
- Check that a photo with a fake `.jpg` extension but non-image content is rejected.
- Check that the uploaded user name is shown safely (try `<b>x</b>` as a name).
- Check that two phones uploading at once create two visitors/photos with no overwrites.
- Check that `post_max_size` smaller than the upload doesn't produce a silent empty form.
