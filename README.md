# CRDB Bank Tanzania — Customer Segmentation for Financial Inclusion

A data science project prototyping a customer segmentation pipeline for retail
and mobile banking (SimBanking-style) customers, built during field work with
the CRDB Data Department.

## Objective

Group customers into behavioral segments — e.g. "urban digital-first users,"
"rural savers," "dormant accounts," "high-frequency traders," "active loan
holders" — using unsupervised learning (PCA + K-Means) on transaction and
account activity data.

**Why it matters to CRDB:** these segments can directly inform targeted
product design (microloans vs. savings products vs. digital-literacy
campaigns), churn/dormancy interventions, and regional financial-inclusion
strategy.

## Project structure

```
crdb-customer-segmentation/
├── data/                    # datasets (synthetic sample committed; real field data goes here)
├── src/
│   ├── generate_data.py     # builds a synthetic dataset for prototyping
│   └── segmentation.py      # feature scaling, PCA, KMeans, segment profiling
├── notebooks/                # exploratory analysis notebooks
├── outputs/                  # generated plots and segment profile tables
├── requirements.txt
└── README.md
```

## Approach

1. **Feature engineering** — transaction frequency, average transaction
   value, mobile login frequency, savings balance, loan activity flag, and
   recency (days since last transaction).
2. **Scaling** — standardize features so no single variable (e.g. balance in
   TZS) dominates the distance metric.
3. **PCA** — reduce to 2 components for visualization and to check for
   multicollinearity between features.
4. **K-Means** — cluster customers; elbow method used to select k.
5. **Profiling** — summarize each segment's average behavior to translate
   clusters into business-readable personas.

## Getting started

```bash
python -m venv venv
source venv/bin/activate  # venv\Scripts\activate on Windows
pip install -r requirements.txt

# 1. generate a synthetic dataset to prototype against
python src/generate_data.py

# 2. run the segmentation pipeline
python src/segmentation.py --k 5
```

Outputs land in `outputs/`: an elbow plot, a PCA scatter plot colored by
segment, and a `segment_profiles.csv` summarizing each segment's average
behavior.

## Using real field-work data

Replace `data/customers_synthetic.csv` with an anonymized export that has the
same column names as `FEATURES` in `src/segmentation.py` (or edit that list
to match whatever fields the Data Department can share). No code changes
needed beyond that to re-run the pipeline on real data.

## Status / Next steps

- [x] Synthetic data generator to prototype the pipeline end-to-end
- [x] PCA + KMeans segmentation with elbow-based k selection
- [ ] Validate segment count and labels with the Data Department against
      known business personas
- [ ] Swap in real (anonymized) field-work data
- [ ] Phase 2: loan default early-warning model using the same customer base

## Author

Aaron — Data Science student, Eastern Africa Statistical Training Centre
(EASTC), Dar es Salaam.
