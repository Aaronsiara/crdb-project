"""
segment_any_data.py

Generalized customer/record segmentation: point it at ANY CSV file and it
will scale the numeric features, reduce with PCA, cluster with KMeans, and
write out labeled data + segment profiles + plots.

Usage:
    python segment_any_data.py --input mydata.csv --k 5
    python segment_any_data.py --input mydata.csv --k 5 --features "age,income,txn_count"
    python segment_any_data.py --input mydata.csv --auto-k

If --features is omitted, all numeric columns are used automatically
(non-numeric columns like names/IDs are ignored for clustering but kept
in the output file).

Outputs (written next to the input file, in a folder named "<input>_segmentation_output"):
    segmented_data.csv     -> original data + a "segment" column
    segment_profiles.csv   -> average feature values per segment
    elbow_plot.png
    pca_clusters.png
"""
import os
import tempfile
if 'HOME' not in os.environ:
    os.environ['HOME'] = tempfile.gettempdir()
    
import argparse
from pathlib import Path

import matplotlib
matplotlib.use("Agg")  # safe for headless environments
import matplotlib.pyplot as plt
import pandas as pd
import seaborn as sns
from sklearn.cluster import KMeans
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler


def load_data(path: Path) -> pd.DataFrame:
    if not path.exists():
        raise FileNotFoundError(f"Input file not found: {path}")
    if path.suffix.lower() == ".csv":
        return pd.read_csv(path)
    elif path.suffix.lower() in (".xlsx", ".xls"):
        return pd.read_excel(path)
    else:
        raise ValueError(f"Unsupported file type: {path.suffix}. Use .csv or .xlsx")


def select_features(df: pd.DataFrame, features_arg: str | None) -> list[str]:
    if features_arg:
        cols = [c.strip() for c in features_arg.split(",")]
        missing = [c for c in cols if c not in df.columns]
        if missing:
            raise ValueError(f"Requested feature columns not found in data: {missing}")
        return cols

    numeric_cols = df.select_dtypes(include="number").columns.tolist()
    if not numeric_cols:
        raise ValueError(
            "No numeric columns found. Pass --features with a comma-separated "
            "list of columns to use for clustering."
        )
    return numeric_cols


def scale_features(df: pd.DataFrame, features: list[str]):
    clean = df[features].copy()
    n_before = len(clean)
    clean = clean.dropna()
    n_after = len(clean)
    if n_after < n_before:
        print(f"Note: dropped {n_before - n_after} rows with missing values in feature columns.")

    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(clean)
    return X_scaled, clean.index


def find_best_k(X_scaled, max_k: int = 10) -> int:
    """Simple elbow heuristic: pick k where inertia improvement drops off."""
    inertias = []
    ks = list(range(1, min(max_k, len(X_scaled) - 1) + 1))
    for k in ks:
        km = KMeans(n_clusters=k, random_state=42, n_init=10)
        km.fit(X_scaled)
        inertias.append(km.inertia_)

    # find the "elbow" as the point of max curvature drop-off
    if len(inertias) < 3:
        return ks[-1]
    diffs = [inertias[i] - inertias[i + 1] for i in range(len(inertias) - 1)]
    diff_ratios = [diffs[i] / diffs[i + 1] if diffs[i + 1] != 0 else 0 for i in range(len(diffs) - 1)]
    best_idx = diff_ratios.index(max(diff_ratios)) + 1  # +1 offset for k starting at 1
    return ks[best_idx]


def run_pipeline(df: pd.DataFrame, features: list[str], k: int | None, auto_k: bool,
                  max_k: int, out_dir: Path):
    out_dir.mkdir(exist_ok=True, parents=True)

    X_scaled, valid_index = scale_features(df, features)

    # Elbow plot (always generated, useful even if k is manually chosen)
    inertias = []
    ks_range = range(1, min(max_k, len(X_scaled) - 1) + 1)
    for kk in ks_range:
        km = KMeans(n_clusters=kk, random_state=42, n_init=10)
        km.fit(X_scaled)
        inertias.append(km.inertia_)
    plt.figure(figsize=(7, 5))
    plt.plot(list(ks_range), inertias, marker="o")
    plt.xlabel("Number of clusters (k)")
    plt.ylabel("Inertia")
    plt.title("Elbow Method for Optimal k")
    plt.tight_layout()
    plt.savefig(out_dir / "elbow_plot.png", dpi=150)
    plt.close()

    if auto_k or k is None:
        k = find_best_k(X_scaled, max_k=max_k)
        print(f"Auto-selected k = {k}")

    # PCA for visualization
    n_components = min(2, X_scaled.shape[1])
    pca = PCA(n_components=n_components, random_state=42)
    X_pca = pca.fit_transform(X_scaled)

    km = KMeans(n_clusters=k, random_state=42, n_init=10)
    labels = km.fit_predict(X_scaled)

    # Attach labels back to the full original dataframe (rows with NaNs get segment = -1)
    df_out = df.copy()
    df_out["segment"] = -1
    df_out.loc[valid_index, "segment"] = labels

    df_out.to_csv(out_dir / "segmented_data.csv", index=False)

    profile = df_out.loc[valid_index].groupby("segment")[features].mean().round(2)
    profile["n_records"] = df_out.loc[valid_index].groupby("segment").size()
    profile = profile.sort_values("n_records", ascending=False)
    profile.to_csv(out_dir / "segment_profiles.csv")

    if n_components == 2:
        plt.figure(figsize=(8, 6))
        sns.scatterplot(x=X_pca[:, 0], y=X_pca[:, 1], hue=labels, palette="tab10", s=40, alpha=0.8)
        plt.xlabel("PC1")
        plt.ylabel("PC2")
        plt.title(f"Segments (k={k}, PCA-reduced view)")
        plt.legend(title="Segment")
        plt.tight_layout()
        plt.savefig(out_dir / "pca_clusters.png", dpi=150)
        plt.close()

    print(f"\nDone. k = {k}, features used: {features}")
    print(f"Outputs written to: {out_dir}\n")
    print("Segment profiles:\n")
    print(profile)

    return df_out, profile


def main():
    parser = argparse.ArgumentParser(description="Segment any dataset using PCA + KMeans.")
    parser.add_argument("--input", required=True, help="Path to input .csv or .xlsx file")
    parser.add_argument("--features", default=None,
                         help="Comma-separated list of columns to cluster on. "
                              "Default: all numeric columns.")
    parser.add_argument("--k", type=int, default=None, help="Number of clusters. Ignored if --auto-k is set.")
    parser.add_argument("--auto-k", action="store_true",
                         help="Automatically pick k using an elbow heuristic.")
    parser.add_argument("--max-k", type=int, default=10, help="Max k to test for elbow plot / auto-k.")
    parser.add_argument("--output-dir", default=None,
                         help="Where to write outputs. Default: <input_name>_segmentation_output/")
    args = parser.parse_args()

    input_path = Path(args.input)
    df = load_data(input_path)
    features = select_features(df, args.features)

    if args.k is None and not args.auto_k:
        print("No --k given, defaulting to --auto-k behavior.")
        args.auto_k = True

    out_dir = Path(args.output_dir) if args.output_dir else input_path.parent / f"{input_path.stem}_segmentation_output"

    run_pipeline(df, features, args.k, args.auto_k, args.max_k, out_dir)


if __name__ == "__main__":
    main()
