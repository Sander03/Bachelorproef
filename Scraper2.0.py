import requests
from bs4 import BeautifulSoup
import mysql.connector

# Connecteren met database
conn = mysql.connector.connect(
    host="localhost",
    user="admin",
    password="admin",
    database="recepten_db"
)
c = conn.cursor()

# API aanspreken om ecosore te berekenen van ingredienten
def get_ecoscore(ingredient_name):
    search_url = f"https://world.openfoodfacts.org/cgi/search.pl?search_terms={ingredient_name}&search_simple=1&json=1"
    response = requests.get(search_url)
    if response.status_code == 200:
        data = response.json()
        products = data.get('products', [])
        if products:
            ecoscore = products[0].get('ecoscore_score', 0)
            return ecoscore
    return 0

# Scraping Dagelijkse Kost website
request = requests.get("https://dagelijksekost.vrt.be").text
soup = BeautifulSoup(request, "html.parser")

# Naam van recept
getAllText = soup.find('p', {'class': "MuiTypography-root MuiTypography-body1 mui-26iacq"})

recipeURL = ""
elements = soup.select('a.MuiTypography-root.MuiTypography-body1.ui-link_link__QfTC4.ui-link_underline-none__jXD3d.mui-tkvuvm[role="link"]')
for index, element in enumerate(elements):
    if index == 4:
        recipeURL = "https://dagelijksekost.vrt.be" + element['href']

# receptinformatie scrapen
receptPage = requests.get(recipeURL).text
receptSoup = BeautifulSoup(receptPage, "html.parser")
getIngredients = receptSoup.find_all('span', {'class': "MuiTypography-root MuiTypography-bodyLarge mui-zm5ze2"})
getIngredientsAmounts = receptSoup.find_all('span', {'class': "MuiTypography-root MuiTypography-titleMedium mui-1hxxsoz"})
ingredienten = []

total_ecoscore = 0

# ingredienten halen en ecoscore van elk ingredient berekenen
for ingredient, amount in zip(getIngredients, getIngredientsAmounts):
    ingredient_name = ingredient.get_text().strip()
    ingredient_amount = amount.get_text().strip()
    ecoscore = get_ecoscore(ingredient_name)
    total_ecoscore += ecoscore  # Add each ingredient's ecoscore to the total
    ingredienten.append(f"{ingredient_name} ({ingredient_amount}) - Ecoscore: {ecoscore}")

ingredienten_text = "\n".join(ingredienten)

# scrapen van bereiding en deftig nummeren
bereiding_steps = receptSoup.find_all('span', {'class': "MuiTypography-root MuiTypography-bodyLarge mui-qomi2d"})
bereiding_text = "\n".join([f"{idx + 1}. {step.get_text().strip()}" for idx, step in enumerate(bereiding_steps)])

# titel van recept
receptTitel = getAllText.get_text()

# data in database steken
c.execute('''
INSERT INTO recepten (naam, ecoscore, ingredienten, bereiding) 
VALUES (%s, %s, %s, %s)
''', (receptTitel, total_ecoscore, ingredienten_text, bereiding_text))
conn.commit()

print(f"Recept '{receptTitel}' toegevoegd aan de database met een totale ecoscore van {total_ecoscore}")
c.close()
conn.close()
