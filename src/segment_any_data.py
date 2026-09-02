import argparse
from pathlib import Path
import matplotlib
matplotlib.use("Agg") 
import matplotlib.pyplot as plt
import pandas as pd
import seaborn as sns
from sklearn.cluster import KMeans
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler

def load_data(path: Path) -> pd.DataFrame:
    """Loads CSV, Excel, or any text file with auto-detected separators."""
    if not path.exists():
        raise FileNotFoundError(f"File not found: {path}")
    
    # Handle Excel files
    if path.suffix.lower() in (".xlsx", ".xls"):
        return pd.read_excel(path)
    
    # Handle ANY text file (CSV, TSV, TXT) and auto-detect if it uses commas, tabs, or semicolons
    return pd.read_csv(path, sep=None, engine="python")

def select_features(df: pd.DataFrame, features_arg: str | None) -> list[str]:
    if features_arg:
        return [c.strip() for c in features_arg.split(",")]
    return df.select_dtypes(include="number").columns.tolist()

def run_pipeline(df: pd.DataFrame, features: list[str], k: int, out_dir: Path):
    out_dir.mkdir(exist_ok=True, parents=True)

    # Clean and scale data
    clean_df = df[features].dropna()
    X_scaled = StandardScaler().fit_transform(clean_df)

    # Cluster
    labels = KMeans(n_clusters=k, random_state=42, n_init=10).fit_predict(X_scaled)

    # Save labeled data
    df_out = df.copy()
    df_out["segment"] = -1
    df_out.loc[clean_df.index, "segment"] = labels
    df_out.to_csv(out_dir / "segmented_data.csv", index=False)

    # Save profiles
    profile = df_out.loc[clean_df.index].groupby("segment")[features].mean().round(2)
    profile["n_records"] = df_out.loc[clean_df.index].groupby("segment").size()
    profile.to_csv(out_dir / "segment_profiles.csv")

    # PCA Plot
    if X_scaled.shape[1] >= 2:
        X_pca = PCA(n_components=2, random_state=42).fit_transform(X_scaled)
        plt.figure(figsize=(8, 6))
        sns.scatterplot(x=X_pca[:, 0], y=X_pca[:, 1], hue=labels, palette="tab10")
        plt.title(f"Segments (k={k})")
        plt.savefig(out_dir / "pca_clusters.png", dpi=150)
        plt.close()

    print(f"Done! Outputs saved to: {out_dir}")

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", required=True)
    parser.add_argument("--features", default=None)
    parser.add_argument("--k", type=int, default=3, help="Number of clusters (default: 3)")
    args = parser.parse_args()

    input_path = Path(args.input)
    df = load_data(input_path)
    features = select_features(df, args.features)
    out_dir = input_path.parent / f"{input_path.stem}_segmentation_output"

    run_pipeline(df, features, args.k, out_dir)

if __name__ == "__main__":
    main()
