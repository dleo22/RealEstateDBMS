# Project Description
# A real estate management system that would allow a real estate company to store and query its listed properties, and the parties related to said listed properties. This database will allow employees of a real estate company to store and query all property listings, both past and present, that the company has been involved in. It will enable an agent to view information about a property, which agent listed said property, the contact information of the seller (or the seller’s lawyer), the building manager’s contact details (if the property is an apartment), and any disclosures that may have been made regarding the property (e.g., history or asbestos, nearby construction, etc.). 

## Comments
### 1. Owns Relationship ###
Changed the relationship between Property and Seller to a many-many relationship as this would fit in-line with our desired goal of storing all information, past and present, about a listed property (including the history of a property's ownership).This change will add an additional table Owns with the folowing schema:

Owns(<ins>**pID**</ins>:INT, <ins>**S_email**</ins>:VARCHAR, boughtPrice:DECIMAL(19,2), dateOfPurchase:DATE, status:VARCHAR)
FK S_email REFERENCES Seller
FK pID REFERENCES Property

This additional table will reduce the number of attributes in our Property table whose schema will be adjusted to:

Property(<ins>pID</ins>:INT, numBed:INT NOT NULL, numBath:FLOAT NOT NULL, govtValuation:DECIMAL(19,2), sqft:FLOAT, postalCode:VARCHAR, street#:VARCHAR, city:VARCHAR, province:VARCHAR, salePrice:DECIMAL(19,2), dateOfSale:DATE, listDate:DATE, listPrice:DECIMAL(19,2), **B_email**:VARCHAR, **A_email**:VARCHAR NOT NULL)
	PK(pID)
	FK B_email REFERENCES Buyer
	FK S_email REFERENCES Seller
