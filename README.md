# DynamicForm PHP
An open source PHP Library for creating forms without HTML, whose structure can be defined by the user of your application instead of being hardcoded by the programmer.
## An example
Let's suppose you have an application where the users can submit form data, for example assignments or surveys. But another user (typically some kind of super user) needs to be free to define this form structure and is not supposed to know nothing about PHP, HTML, HTTP post/get requests, SQL and so on. **That's where DynamicForm comes into action.**
## With DynamicForm you can...
1. Let the user define the structure of the form visually
1. After the structure is defined, display the form on screen
1. Validate the data before storing on database, and this includes file uploads
1. Have the form content (as well the structure) saved in your database
## Changelog
- 2021-05-11 **(feature)** The `outputStructureTable` method now receives one parameter, the structure name `$strname`. No more parameters for classes names. If the client needs style modifications, it can do throgh the ids of elements. This function now outputs 4 HTML elements. 1: The toolbar, in a div with its own id (new), 2: the structure table with its id, 3: the HTML hidden `input` element with the value that can be used in a form, and 4: the javascript code all contained in one script tag.
- 2021-05-11 **(feature)** Table structure icons are svgs. The function `find_relative_path` was removed, since there is no need to search for png files. There is no more http requests in the strcuture table.
- 2021-05-01 **(bug fix)** function `find_relative_path` is static
- 2020-12-16 **(bug fix)** The html output of a Bigtext is now enclosed with `<pre>...</pre>`, so the spacing is preserved.
