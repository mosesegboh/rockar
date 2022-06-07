const page = require('webpage').create(),
    system = require('system'),
    fs = require('fs');
page.paperSize = {
    format: 'A4',
    orientation: 'portrait',
    margin: {
        top: "1cm",
        bottom: "1cm",
        left: "1cm",
        right: "1cm",

    },
    footer: {
        height: "1cm",
        contents: phantom.callback(function (pageNum, numPages) {
            return "<style>.pdffooter{margin: 0 0cm 0 0.2cm; font-size: 1em;}" +
                ".pdffooter > div{font-size: 0.5em;display: inline-block;width:33%;}" +
                ".center{text-align: center;}" +
                "</style>"+
                "<div class='pdffooter'><div>" +
                "Page | " + pageNum + " ###FOOTER_NO### " +
                "<br>"+
                "Revision date: ###DATE###"+
                "</div>"+
                "<div>" +
                "<div class='center'>" +
                "###DATETIME###"+
                "</div>"+
                "</div>"+
                "</div>";
        })
    },
    header: {
        height: "2cm",
        contents: phantom.callback(function(pageNum, numPages) {
            return "<style>.pdfheader{font-size: 0.65em;}" +
                ".pdfheader table{font-size: 0.5em;display: inline-block; border-collapse: collapse;}" +
                ".center{text-align: center;}" +
                ".border{border:1px solid #000;}" +
                ".float-left{ float: left; }"+
                ".float-right{ float: right; display: inline-block;}"+
                ".no-margin{margin:0; padding: 0; vertical-align: top}"+
                "table{width: 15%}"+
                "td{ padding: 2px;}"+
                ".pdfheader table td {vertical-align: top;}"+
                "</style>"+
                "<div class='pdfheader'>" +
                "<table class='table center float-left no-margin'>"+
                    "<tr>"+
                        "<td>"+
                        "<strong>"+
                        "OFFER TO PURCHASE" +
                        "</strong>"+
                        "</td>"+
                    "</tr>"+
                    "<tr>"+
                        "<td valign='top' class='border'>"+
                            "###Number###"+
                        "</td>"+
                    "</tr>"+
                    "<tr>"+
                        "<td valign='top' class='border'>"+
                            "###DATE###"+
                        "</td>"+
                    "</tr>"+
                "</table>"+
                    "<div>" +
                    "<img class='float-right'src='data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9zb2RpcG9kaS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIgogICB4bWxuczppbmtzY2FwZT0iaHR0cDovL3d3dy5pbmtzY2FwZS5vcmcvbmFtZXNwYWNlcy9pbmtzY2FwZSIKICAgd2lkdGg9IjgwMCIKICAgaGVpZ2h0PSIzODEuMTk4NTgiCiAgIHZpZXdCb3g9IjAgMCA2NDAuMDAwMDIgMzA0Ljk1ODg3IgogICB2ZXJzaW9uPSIxLjEiCiAgIGlkPSJzdmcyIgogICBpbmtzY2FwZTp2ZXJzaW9uPSIwLjkxIHIxMzcyNSIKICAgc29kaXBvZGk6ZG9jbmFtZT0iQk1XIEdyb3VwLnN2ZyI+CiAgPG1ldGFkYXRhCiAgICAgaWQ9Im1ldGFkYXRhMjkzIj4KICAgIDxyZGY6UkRGPgogICAgICA8Y2M6V29yawogICAgICAgICByZGY6YWJvdXQ9IiI+CiAgICAgICAgPGRjOmZvcm1hdD5pbWFnZS9zdmcreG1sPC9kYzpmb3JtYXQ+CiAgICAgICAgPGRjOnR5cGUKICAgICAgICAgICByZGY6cmVzb3VyY2U9Imh0dHA6Ly9wdXJsLm9yZy9kYy9kY21pdHlwZS9TdGlsbEltYWdlIiAvPgogICAgICAgIDxkYzp0aXRsZT48L2RjOnRpdGxlPgogICAgICA8L2NjOldvcms+CiAgICA8L3JkZjpSREY+CiAgPC9tZXRhZGF0YT4KICA8c29kaXBvZGk6bmFtZWR2aWV3CiAgICAgcGFnZWNvbG9yPSIjZmZmZmZmIgogICAgIGJvcmRlcmNvbG9yPSIjNjY2NjY2IgogICAgIGJvcmRlcm9wYWNpdHk9IjEiCiAgICAgb2JqZWN0dG9sZXJhbmNlPSIxMCIKICAgICBncmlkdG9sZXJhbmNlPSIxMCIKICAgICBndWlkZXRvbGVyYW5jZT0iMTAiCiAgICAgaW5rc2NhcGU6cGFnZW9wYWNpdHk9IjAiCiAgICAgaW5rc2NhcGU6cGFnZXNoYWRvdz0iMiIKICAgICBpbmtzY2FwZTp3aW5kb3ctd2lkdGg9IjEyODAiCiAgICAgaW5rc2NhcGU6d2luZG93LWhlaWdodD0iNzM4IgogICAgIGlkPSJuYW1lZHZpZXcyOTEiCiAgICAgc2hvd2dyaWQ9ImZhbHNlIgogICAgIGZpdC1tYXJnaW4tdG9wPSIwIgogICAgIGZpdC1tYXJnaW4tbGVmdD0iMCIKICAgICBmaXQtbWFyZ2luLXJpZ2h0PSIwIgogICAgIGZpdC1tYXJnaW4tYm90dG9tPSIwIgogICAgIHVuaXRzPSJweCIKICAgICBpbmtzY2FwZTp6b29tPSIwLjY3MjgwNjMyIgogICAgIGlua3NjYXBlOmN4PSIxMTIuNDk5NDkiCiAgICAgaW5rc2NhcGU6Y3k9IjIyLjI4NjE5OCIKICAgICBpbmtzY2FwZTp3aW5kb3cteD0iLTgiCiAgICAgaW5rc2NhcGU6d2luZG93LXk9Ii04IgogICAgIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjEiCiAgICAgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0ic3ZnMiIgLz4KICA8ZGVmcwogICAgIGlkPSJkZWZzNCI+CiAgICA8Y2xpcFBhdGgKICAgICAgIGlkPSJjbGlwMiI+CiAgICAgIDxwYXRoCiAgICAgICAgIGQ9Im0gMjkuMDU0Njg4LDc2Ny4zODY3MiA2Mi45NDE0MDYsMCAwLC0zMC44MzIwMyAtNjIuOTQxNDA2LDAgeiIKICAgICAgICAgaWQ9InBhdGg2NyIKICAgICAgICAgaW5rc2NhcGU6Y29ubmVjdG9yLWN1cnZhdHVyZT0iMCIgLz4KICAgIDwvY2xpcFBhdGg+CiAgPC9kZWZzPgogIDxnCiAgICAgaWQ9ImczNzE3IgogICAgIHRyYW5zZm9ybT0ibWF0cml4KDEwLjY5NTkxMywwLDAsMTAuNjk1OTEzLC0zMjkuNDQyNDcsLTc4ODMuNjM5OSkiPgogICAgPGcKICAgICAgIGNsaXAtcGF0aD0idXJsKCNjbGlwMikiCiAgICAgICBpZD0iZzEwMyIKICAgICAgIHN0eWxlPSJjbGlwLXJ1bGU6bm9uemVybyI+CiAgICAgIDxwYXRoCiAgICAgICAgIHN0eWxlPSJmaWxsOiMwMDAwMDA7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgIGQ9Im0gODMuMDU4NTk0LDc1NC43MTA5NCAyLjY2Nzk2OSwwIGMgMC42NDg0MzcsMCAxLjE2MDE1NiwwLjE2NDA2IDEuNTMxMjUsMC40ODgyOCAwLjM3NSwwLjMyNDIyIDAuNTYyNSwwLjc4NTE1IDAuNTYyNSwxLjM3NSAwLDAuNTk3NjUgLTAuMjAzMTI1LDEuMDgyMDMgLTAuNjA5Mzc1LDEuNDQxNCAtMC4zOTA2MjUsMC4zNDM3NSAtMC44OTQ1MzIsMC41MTE3MiAtMS41MTU2MjUsMC41MTE3MiBsIC0yLjYzNjcxOSwwIHogbSAwLDEwLjU2NjQgMCwtNC41ODk4NCAyLjkxNDA2MiwwIGMgMS4zOTA2MjUsMCAyLjUwNzgxMywtMC4zNTkzOCAzLjM0NzY1NywtMS4wNzgxMyAwLjg3ODkwNiwtMC43NTM5IDEuMzE2NDA2LC0xLjc2MTcxIDEuMzE2NDA2LC0zLjAyNzM0IDAsLTEuMjQ2MDkgLTAuMzc4OTA2LC0yLjIzMDQ3IC0xLjE0NDUzMSwtMi45NTcwMyAtMC43NjE3MTksLTAuNzE0ODQgLTEuODI0MjE5LC0xLjA3MDMxIC0zLjE3OTY4OCwtMS4wNzAzMSBsIC01Ljk3NjU2MiwwIDAsMTIuNzIyNjUgeiBtIC0xNC44MDA3ODEsLTQuOTYwOTMgYyAwLDEuNjA1NDYgMC40MjE4NzUsMi44NjMyOCAxLjI2OTUzMSwzLjc4MTI1IDAuOTEwMTU2LDAuOTg4MjggMi4yNjk1MzEsMS40ODQzNyA0LjA3ODEyNSwxLjQ4NDM3IDEuNzU3ODEyLDAgMy4wODU5MzcsLTAuNDU3MDMgMy45ODgyODEsLTEuMzY3MTkgMC45MjE4NzUsLTAuOTI5NjggMS4zODI4MTMsLTIuMjI2NTYgMS4zODI4MTMsLTMuODk4NDMgbCAwLC03Ljc2MTcyIC0yLjc1LDAgMCw3Ljk0NTMxIGMgMCwxLjg2NzE5IC0wLjg2NzE4OCwyLjgwMDc4IC0yLjU5NzY1NywyLjgwMDc4IC0xLjc0NjA5MywwIC0yLjYyMTA5MywtMC45MzM1OSAtMi42MjEwOTMsLTIuODAwNzggbCAwLC03Ljk0NTMxIC0yLjc1LDAgeiBtIC00LjcwMzEyNSwtNC41ODIwNCBjIDAuNTkzNzUsMC43ODEyNSAwLjg5MDYyNSwxLjg0Mzc1IDAuODkwNjI1LDMuMTkxNDEgMCwxLjQ2ODc1IC0wLjM1OTM3NSwyLjU5NzY2IC0xLjA3NDIxOSwzLjM4NjcyIC0wLjU4OTg0NCwwLjY1NjI1IC0xLjM3MTA5NCwwLjk4ODI4IC0yLjMzMjAzMSwwLjk4ODI4IC0xLjA3NDIxOSwwIC0xLjkxNDA2MywtMC40MDIzNCAtMi41MTk1MzIsLTEuMjAzMTIgLTAuNTgyMDMxLC0wLjc3NzM1IC0wLjg3NSwtMS44MzIwNCAtMC44NzUsLTMuMTcxODggMCwtMS4zNDc2NiAwLjI5Mjk2OSwtMi40MTAxNiAwLjg3NSwtMy4xOTE0MSAwLjYwOTM3NSwtMC44MDg1OSAxLjQzNzUsLTEuMjEwOTMgMi40ODgyODIsLTEuMjEwOTMgMS4wODIwMzEsMCAxLjkzMzU5MywwLjQwMjM0IDIuNTQ2ODc1LDEuMjEwOTMgbSAtNy4wNzgxMjUsLTEuNzAzMTIgYyAtMS4wODk4NDQsMS4xNzE4NyAtMS42MzI4MTMsMi44MDg1OSAtMS42MzI4MTMsNC44OTg0NCAwLDIuMjg5MDYgMC42NTIzNDQsNC4wMTU2MiAxLjk1MzEyNSw1LjE4NzUgMS4wODIwMzEsMC45NzY1NiAyLjQ5NjA5NCwxLjQ2NDg0IDQuMjQyMTg4LDEuNDY0ODQgMS45NDUzMTIsMCAzLjQ2ODc1LC0wLjU5Mzc1IDQuNTY2NDA2LC0xLjc4MTI1IDEuMDkzNzUsLTEuMTc1NzggMS42NDA2MjUsLTIuNzk2ODcgMS42NDA2MjUsLTQuODcxMDkgMCwtMi4wNzgxMyAtMC41NDY4NzUsLTMuNzE0ODUgLTEuNjQwNjI1LC00Ljg5ODQ0IC0xLjA5Mzc1LC0xLjE5MTQxIC0yLjU5NzY1NiwtMS43ODEyNSAtNC41MTU2MjUsLTEuNzgxMjUgLTEuOTc2NTYzLDAgLTMuNTE1NjI1LDAuNTkzNzUgLTQuNjEzMjgxLDEuNzgxMjUgbSAtOS45NDE0MDcsMC42Nzk2OSAyLjY1MjM0NCwwIGMgMC43MzA0NjksMCAxLjI3MzQzOCwwLjEyODkgMS42MzI4MTMsMC4zOTA2MiAwLjM2MzI4MSwwLjI2MTcyIDAuNTQyOTY4LDAuNjk1MzEgMC41NDI5NjgsMS4zMDA3OCAwLDEuMTc5NjkgLTAuNzI2NTYyLDEuNzY5NTMgLTIuMTc1NzgxLDEuNzY5NTMgbCAtMi42NTIzNDQsMCB6IG0gMCwxMC41NjY0IDAsLTQuOTYwOTMgMi43MzQzNzUsMCAyLjMxNjQwNyw0Ljk2MDkzIDMuMDgyMDMxLDAgLTIuNzI2NTYzLC01LjQxNDA2IGMgMS40OTIxODgsLTAuNjkxNDEgMi4yMzgyODIsLTEuODI4MTIgMi4yMzgyODIsLTMuNDEwMTYgMCwtMS4xMTMyOCAtMC4yODUxNTcsLTEuOTg4MjggLTAuODU1NDY5LC0yLjYzNjcxIC0wLjczMDQ2OSwtMC44NDM3NSAtMS44NzUsLTEuMjYxNzIgLTMuNDI5Njg4LC0xLjI2MTcyIGwgLTYuMTAxNTYyLDAgMCwxMi43MjI2NSB6IG0gLTUuMzI4MTI1LC0xMS40NDUzMSBjIC0wLjkxMDE1NiwtMS4wNTQ2OSAtMi4zMTY0MDYsLTEuNTgyMDMgLTQuMjE4NzUsLTEuNTgyMDMgLTEuOTQ1MzEyLDAgLTMuNDY0ODQzLDAuNjA5MzcgLTQuNTY2NDA2LDEuODI4MTIgLTEuMDgyMDMxLDEuMTk5MjIgLTEuNjIxMDk0LDIuODUxNTcgLTEuNjIxMDk0LDQuOTYwOTQgMCwyLjMzOTg1IDAuNjQ4NDM4LDQuMDg1OTQgMS45NTMxMjUsNS4yMzQzOCAwLjk4NDM3NSwwLjg3MTA5IDIuMjUzOTA3LDEuMzA4NTkgMy44MTY0MDcsMS4zMDg1OSAxLjY5NTMxMiwwIDIuOTcyNjU2LC0wLjUzMTI1IDMuODI4MTI1LC0xLjU4OTg0IGwgMC4yODkwNjIsMS4yODUxNSAxLjc1NzgxMywwIDAsLTYuNzMwNDcgLTQuNzIyNjU3LDAgMCwxLjk5MjE5IDIuMDQyOTY5LDAgMCwwLjU4NTk0IGMgMCwwLjY1MjM0IC0wLjI3NzM0NCwxLjE4NzUgLTAuODMyMDMxLDEuNTk3NjYgLTAuNTI3MzQ0LDAuMzg2NzEgLTEuMTk5MjE5LDAuNTc4MTIgLTIuMDExNzE5LDAuNTc4MTIgLTEuMDU4NTk0LDAgLTEuODc1LC0wLjM2MzI4IC0yLjQ0NTMxMiwtMS4wODk4NCAtMC41ODIwMzIsLTAuNzMwNDcgLTAuODc1LC0xLjc4NTE2IC0wLjg3NSwtMy4xNjQwNyAwLC0xLjQxNzk2IDAuMjc3MzQzLC0yLjUxOTUzIDAuODMyMDMxLC0zLjMwNDY4IDAuNTc4MTI1LC0wLjgxMjUgMS4zOTQ1MzEsLTEuMjE4NzUgMi40NTcwMzEsLTEuMjE4NzUgMS40NzY1NjMsMCAyLjQ1NzAzMSwwLjc2OTUzIDIuOTQxNDA2LDIuMzE2NCBsIDIuNTc4MTI1LC0wLjYyODkgYyAtMC4yNzM0MzcsLTAuOTgwNDcgLTAuNjc1NzgxLC0xLjc3NzM1IC0xLjIwMzEyNSwtMi4zNzg5MSIKICAgICAgICAgaWQ9InBhdGgxMDUiCiAgICAgICAgIGlua3NjYXBlOmNvbm5lY3Rvci1jdXJ2YXR1cmU9IjAiIC8+CiAgICAgIDxwYXRoCiAgICAgICAgIHN0eWxlPSJmaWxsOiMwMDAwMDA7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgIGQ9Im0gNjQuMDk3NjU2LDc0OS43OTY4NyAyLjM2NzE4OCwtOC45ODQzNyAyLjI0MjE4Nyw4Ljk4NDM3IDIuOTgwNDY5LDAgMy4wODk4NDQsLTEyLjcyNjU2IC0yLjgwMDc4MSwwIC0xLjkyMTg3NSw5LjE3OTY5IC0yLjE3MTg3NSwtOS4xNzk2OSAtMi43MjI2NTcsMCAtMi4yNDYwOTMsOS4xNzk2OSAtMS44NzEwOTQsLTkuMTc5NjkgLTIuODcxMDk0LDAgMi45MTc5NjksMTIuNzI2NTYgeiBtIC0xOC4xNDA2MjUsMC4wMDggMCwtOS4wNjY0MSAzLjAxNTYyNSw5LjA2NjQxIDIuMjUsMCAzLC05LjA2NjQxIDAsOS4wNjY0MSAyLjYwNTQ2OSwwIDAsLTEyLjcyMjY2IC0zLjYwMTU2MiwwIC0zLjA2NjQwNyw5LjQ0OTIyIC0zLjEwNTQ2OCwtOS40NDkyMiAtMy42ODM1OTQsMCAwLDEyLjcyMjY2IHogbSAtMTIsLTEwLjU2NjQxIDIuODk4NDM4LDAgYyAwLjU2NjQwNiwwIDAuOTk2MDk0LDAuMDY2NCAxLjI5Njg3NSwwLjE5OTIyIDAuNDg4MjgxLDAuMjE0ODQgMC43MzA0NjksMC42MjEwOSAwLjczMDQ2OSwxLjIxODc1IDAsMC40OTYwOSAtMC4xNjAxNTcsMC44NTkzNyAtMC40NzY1NjMsMS4wODk4NCAtMC4zMDg1OTQsMC4yMzA0NyAtMC44MjQyMTksMC4zNDM3NSAtMS41NTA3ODEsMC4zNDM3NSBsIC0yLjg5ODQzOCwwIHogbSAwLDQuOTk2MDkgMi44NjcxODgsMCBjIDAuNzU3ODEyLDAgMS4zMjQyMTksMC4wOTc3IDEuNjk5MjE5LDAuMjg5MDcgMC40ODgyODEsMC4yNSAwLjczMDQ2OCwwLjcyMjY1IDAuNzMwNDY4LDEuNDA2MjUgMCwwLjU3NDIyIC0wLjE1MjM0MywwLjk4ODI4IC0wLjQ2MDkzNywxLjI0NjA5IC0wLjM3NSwwLjMxMjUgLTEuMDMxMjUsMC40Njg3NSAtMS45Njg3NSwwLjQ2ODc1IGwgLTIuODY3MTg4LDAgeiBtIDIuODE2NDA3LDUuNTcwMzIgYyAzLjUsMCA1LjI1LC0xLjI3NzM1IDUuMjUsLTMuODI0MjIgMCwtMS40MTc5NyAtMC42Nzk2ODgsLTIuMzk4NDQgLTIuMDQyOTY5LC0yLjk0MTQxIDEuMDkzNzUsLTAuNTI3MzQgMS42NDA2MjUsLTEuNDM3NSAxLjY0MDYyNSwtMi43MjY1NiAwLC0xLjEzMjgxIC0wLjQ0OTIxOSwtMS45ODA0NyAtMS4zNDM3NSwtMi41NDY4OCAtMC43MTQ4NDQsLTAuNDU3MDMgLTEuNjgzNTk0LC0wLjY4MzU5IC0yLjkxMDE1NiwtMC42ODM1OSBsIC02LjExMzI4MiwwIDAsMTIuNzIyNjYgeiIKICAgICAgICAgaWQ9InBhdGgxMDciCiAgICAgICAgIGlua3NjYXBlOmNvbm5lY3Rvci1jdXJ2YXR1cmU9IjAiIC8+CiAgICA8L2c+CiAgPC9nPgo8L3N2Zz4=' style='height:1.3cm;margin-top:-0.23cm;float: right'>"+
                    "</div>" +
                "</div>";
        })
    },
};
page.settings.dpi = "96";
page.content = fs.read(system.args[1]);
const output = system.args[2];
window.setTimeout(function () {
    page.render(output, {format: 'pdf'});
    phantom.exit(0);
}, 2000);
