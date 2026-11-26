# Add images and documents to a page

[Documentation home](README.md) · [Create your first page](first-page.md)

## Summary

Upload a picture or PDF once, give it an object name, then use that name in any page that needs it. The resource list's **U** opens the file upload and **M** opens its metadata. The page source controls where the resource appears.

**For** editors who can create media objects and edit a referring page. **Version** u5CMS 12.10.5. The examples use an RGB JPEG and a PDF, both usable on the maintained development branches.

## Add a picture with enlargement

1. In the far-right column choose **I** (Images). Use the **std Images** view. If the heading says **free Images**, use the link to standard images. Choose **new**, enter `workshopmap`, wait for **name is available** and select **insert new item**.
2. Find `workshopmap` in the image list. Choose **U** on its row. In the first upload field select the RGB JPEG to use across languages. Choosing the file starts the upload. Wait for the filename to appear under **Files already uploaded here**.
3. Choose **M** on the same row. In the language you are publishing, enter **alt text** describing the information in the image, a **short caption** for the page and, if useful, a **long caption** for the enlarged view. Choose **save & close**.
4. In the source of your page, on its own line, insert the following and save with **s**.

   ```text
   [:::::workshopmap]
   ```

5. Open the saved page using the editor's **letter P**. The standard template centres the large picture with its short caption. Select the picture to see the enlarged version. The image's alternative text should still make sense to someone who cannot see it.

## Link a PDF

1. Choose **D** (Documents), then **new**. Create `workshopguide`. On its row choose **U** and select the PDF in the main upload field. Use a language-specific field if that language needs its own file.
2. On the same row choose **M**. Set **link text** for the languages you use, then choose **save & close**. A label written directly in a page takes precedence for that particular link.
3. Add this line to the referring page, save it and open the link from the visitor page. Check the title or edition inside the PDF.

   ```text
   [Download the workshop guide (PDF):workshopguide]
   ```

![Documents list showing the workshopguide resource. M opens its metadata and U uploads or replaces its file.](images/document-controls.png)

## Replace a shared file

Find the existing object under **I** or **D**. Choose **U** on its row and upload the replacement into the same language slot. Pages referring to that object now use the replacement. Open one of those pages in the relevant language and follow the link. To keep both editions in circulation, give the second edition its own object name.

| What you see | Where to look |
| --- | --- |
| The page shows a link but the download is missing | In **U**, check the file actually listed for that object and language. Creating an object reserves its name. The upload supplies the file. |
| The page shows the previous edition in just one language | Check whether that language has its own uploaded file. Replacing the main file leaves the separate language edition in place. |
| A standard image has a caption but no useful alternative text | Open **M**, choose the correct language and fill in **alt text**. The short caption and alternative text have separate fields. |

File access can depend on the pages that reference the object. For a restricted document, check which other pages link to it and open the download with the intended reader permissions. See [page visibility](navigation.md#set-page-visibility) and the [original illustrated short reference](../u5admin/u5shortreference.pdf).
