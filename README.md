# Wiskunde Woordenboek
An open source maths dictionary (encyclopedia) written in pure PHP.

## Codebase
The website is written in pure HTML, CSS, JavaScript and PHP, with no framework used. This was mostly done to see how 
far one can get with a bare-bones setup. The website would probably have been easier to make with the use of a
framework. A side effect of this is that the raw HTML responses are quite clean, which I suppose is nice.

## Why is the website in Dutch?
The website is in Dutch because I am Dutch. I know more about how mathematics is taught here in The Netherlands than
elsewhere. This allows me to target the Dutch audience better than if the website were in English. I also have
experience teaching mathematics to Dutch high school students.

## Why is the code in English?
The code is in English because it is generally more natural to program in English. In Dutch, some phrases would be
awkward to translate. Furthermore, the code is more accessible in English. People who do not speak Dutch can still
contribute to the codebase of the website if they wish. That is also the reason this README is in English.
English-speaking people visiting my GitHub profile will still be able to figure out what this project is about.

## Contributing
All contributions are welcome. I distinguish two kinds of contributions:

1. **Codebase contributions.** Contributions towards the underlying code that backs this website. Do this by opening a
pull request. If your contribution is large, consider opening an issue first.

2. **Content-related contributions.** Contributions towards explanations of mathematical terms. Do this by filling out
the form that can be found underneath the explanation for each term. If a term does not exist yet, simply browse to the
URL the word would be located at.

## Local setup
It is recommended to use PhpStorm for testing the website locally. This is because the included run configurations are
for PhpStorm specifically.

To test the website locally, do the following:
1. Clone it:
   ```shell
   git clone https://github.com/xpple/wiskundewoordenboek.nl
   cd wiskundewoordenboek.nl
   ```
2. Set the environment variable `WW_HOST` to your device's local IP address.
3. Specify to use the production database:
   ```shell
   echo "WW_ENVIRONMENT=PROD" > .env
   ```
4. Open the `main` directory as PhpStorm project, and attach `api` as project.
5. Run the "launch API" task.
6. Run the "launch main" task.
