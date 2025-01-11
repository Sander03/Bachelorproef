import requests
from bs4 import BeautifulSoup
import time
import translators as ts
from deep_translator import GoogleTranslator
import mysql.connector
from dotenv import load_dotenv
import os

load_dotenv()
conn = mysql.connector.connect(
    host=os.getenv("DB_HOST"),
    user=os.getenv("DB_USER"),
    password=os.getenv("DB_PASSWORD"),
    database=os.getenv("DB_NAME")
)

def get_ecoscore(ingredient_naam):
    pogingen = 5
    ecoscores = []
    ingredient_translated = GoogleTranslator(source='nl', target='en').translate(ingredient_naam)
    ingredient_translated = ingredient_translated.replace(" ", "%20")
    for poging in range(pogingen):
        try:
            response = requests.get(f"https://world.openfoodfacts.org/cgi/search.pl?search_terms={ingredient_translated}&search_simple=1&json=1")
            if response.status_code == 200:
                data = response.json()
                producten = data.get('products', [])
                for product in producten:
                    ecoscore = product.get('ecoscore_score')
                    if ecoscore is not None:
                        ecoscores.append(min(ecoscore, 100))
                break
            elif response.status_code == 429:
                time.sleep(60)
        except:
            return None

    if ecoscores:
        return round(sum(ecoscores) / len(ecoscores))
    return None

def get_alternatives(ingredient_naam):
    vertaald_ingredient = GoogleTranslator(source='nl', target='en').translate(ingredient_naam)
    spoonacular_url = f"https://api.spoonacular.com/food/ingredients/substitutes?ingredientName={vertaald_ingredient}&apiKey=bab1493abe2a45e79e5c7adc5fbfeb5b"
    response = requests.get(spoonacular_url)
    alternatieven_ecoscores = []

    if response.status_code == 200:
        data = response.json()
        substitutes = data.get('substitutes', [])

        for sub in substitutes:
            if "=" in sub:
                sub = sub.split("=")[-1].strip()
            translated_sub = GoogleTranslator(source='en', target='nl').translate(sub)
            laatsteWoord = sub.split()[-1]
            alternative_ecoscore = get_ecoscore(laatsteWoord)

            ecoscore_text = alternative_ecoscore if alternative_ecoscore is not None else "Geen beschikbaar"
            alternatieven_ecoscores.append(f"- {translated_sub} - Ecoscore: {ecoscore_text}")

    return alternatieven_ecoscores if alternatieven_ecoscores else ["- Geen alternatieven beschikbaar"]

request = requests.get("https://dagelijksekost.vrt.be/").text
soup = BeautifulSoup(request, "html.parser")
# MuiTypography-root MuiTypography-body1 ui-link_link__QfTC4 ui-link_underline-none__jXD3d mui-11b63c0
firstRecentRecipe = soup.find_all('a', {'class': "MuiTypography-root MuiTypography-body1 ui-link_link__QfTC4 ui-link_underline-none__jXD3d mui-11b63c0"})
if len(firstRecentRecipe) > 9:
    recipeURL = "https://dagelijksekost.vrt.be" + firstRecentRecipe[9]['href']
else:
    exit(1)

receptPage = requests.get(recipeURL).text
receptSoup = BeautifulSoup(receptPage, "html.parser")
getIngredients = receptSoup.find_all('span', {'class': "MuiTypography-root MuiTypography-bodyLarge mui-1h9m71h"})
# MuiTypography-root MuiTypography-bodyLarge mui-1h9m71h
# MuiTypography-root MuiTypography-bodyLarge mui-zm5ze2
getIngredientsAmounts = receptSoup.find_all('span', {'class': "MuiTypography-root MuiTypography-titleMedium mui-vcwjts"})
getReceptTitel = receptSoup.find('h1', {'class': "MuiTypography-root MuiTypography-displaySmall mui-w15koq"})
# MuiTypography-root MuiTypography-displaySmall mui-w15koq
receptTitel = getReceptTitel.text
ingredienten = []

getImageElement = receptSoup.find_all('img')
imageLink = "https://dagelijksekost.vrt.be/" + getImageElement[6]['src'] if len(getImageElement) > 7 else "Geen afbeelding beschikbaar"

total_ecoscore = 0

for ingredient, amount in zip(getIngredients, getIngredientsAmounts):
    ingredient_naam = ingredient.get_text().strip()
    original_name = ingredient_naam

    if "look" in ingredient_naam.lower():
        ingredient_naam = "knoflook"

    if "eiwitten" in ingredient_naam.lower() or "eierdooier" in ingredient_naam.lower():
        ingredient_naam = "ei"

    ingredient_amount = amount.get_text().strip()
    ecoscore = get_ecoscore(ingredient_naam)
    time.sleep(1)

    total_ecoscore += ecoscore if isinstance(ecoscore, (int, float)) else 0
    alternatives = get_alternatives(ingredient_naam)
    alternatives_text = "\n".join(alternatives) if alternatives else "Geen alternatieven beschikbaar"
    ingredienten.append(f"{ingredient_naam} ({ingredient_amount}) - Ecoscore: {ecoscore}\nAlternatieven:\n{alternatives_text}\n-------")

ingredienten_text = "\n".join(ingredienten)

bereiding_steps = receptSoup.find_all('span', {'class': "MuiTypography-root MuiTypography-bodyLarge mui-qybxi1"})
bereiding_text = "\n".join([f"{index + 1}. {step.get_text().strip()}" for index, step in enumerate(bereiding_steps)])

total_ecoscore = round(total_ecoscore)

cursor = conn.cursor()

query = '''
INSERT INTO recepten (naam, ecoscore, ingredienten, bereiding, image_url)
VALUES (%s, %s, %s, %s, %s)
'''
cursor.execute(query, (receptTitel, total_ecoscore, ingredienten_text, bereiding_text, imageLink))
conn.commit()

print("Recept succesvol opgeslagen in de database.")

cursor.close()
conn.close()
