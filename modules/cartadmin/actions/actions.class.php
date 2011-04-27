<?php
class cartadminActions extends sfActions
{

	public function executeProductUnlink(sfWebRequest $request) {
		if (($request->hasParameter("categoryid")) && ($request->hasParameter("productid"))) {
			$prodcat = Doctrine_Query::create()->from('ProductCategory pc')->where('product_id = ?', $request->getParameter("productid"))->andWhere('category_id=?',$request->getParameter("categoryid") )->execute();
			if (isset($prodcat)) {
				$prodcat->delete();
				cart::update_node($request->getParameter("categoryid"));
			}
		}
		return true;
	}

	public function executeProductLink(sfWebRequest $request) {
		if (($request->hasParameter("categoryid")) && ($request->hasParameter("productid"))) {

			$prodcat = new ProductCategory();
			$prodcat->setProductId($request->getParameter("productid"));
			$prodcat->setCategoryId($request->getParameter("categoryid"));
			$prodcat->save();
			cart::update_node($request->getParameter("categoryid"));
			return true;
		}
	}

	public function executeProductSearch(sfWebRequest $request) {
		$filter = $request->getParameter('term');
		$results = Doctrine_Core::getTable('product')->search($filter . "*");

		$ids = array();
		foreach ($results as $result) {
			$ids[] = $result['id'];
		}
		if (count($ids) > 0) {
			$q = Doctrine_Query::create()->from('product p')->whereIn('p.id', $ids);
			$this->products = $q->execute();

			$return_arr = array();
			foreach ($this->products as $product) {
				$row_array['id'] = $product->getId();
				$row_array['value'] = $product->getName();
				array_push($return_arr,$row_array);
			}
			 
			echo json_encode($return_arr);
		}
		return true;
	}

	public function executeProductIndex(sfWebRequest $request) {

		if ($request->hasParameter('filter') && ($request->getParameter('filter') != "")) {
			$prodIds = Doctrine_Query::create()->select('product_id')->from('ProductCategory')->where('category_id=?',$request->getParameter('filter'))->execute();
			if (count($prodIds) > 0) {
				foreach ($prodIds as $i => $prodId) {
					$ids[$i] = $prodId['product_id'];
				}
				$this->products = Doctrine::getTable('Product')->createQuery('a')->whereIn('id', $ids)->execute();
				$this->filter = $request->getParameter('filter');
			}
			else {
				$this->products = null;
			}
		}
		else {
			$this->products = Doctrine::getTable('Product')->getProductsWithCategories();
		}
		 
		 
	}

	public function executeCategoryIndex(sfWebRequest $request) {
		$this->categories = Doctrine::getTable('Category')->createQuery('a')->execute();
	}

	public function executeInsertCategory(sfWebRequest $request) {
		if (($request->hasParameter("categoryid")) && ($request->hasParameter("categoryname"))) {
			$parent = Doctrine_Core::getTable('Category')->findOneById($request->getParameter("categoryid"));
			$category = new Category();
			$category->setName($request->getParameter("categoryname"));
			$category->setActive(true);
			$category->getNode()->insertAsLastChildOf($parent);
			return true;
		}
	}

	public function executeCategoryUpdateTree(sfWebRequest $request) {
		$rows = 0;
		if ($request->getParameter('tree')) {
			$nodes = $request->getParameter('tree');
			foreach ($nodes as $node) {
				if ($node['item_id'] != "root") {
					$rowcount = Doctrine_Query::create()->update('Category')->set('lft', $node['left'])->set('rgt', $node['right'])->set('level', $node['depth'])->where('id = ?', $node['item_id'])->execute();
					$rows += $rowcount;
				}
			}
			echo $rows . " row(s) updated.";
		}
		return true;
	}

	public function executeCouponIndex(sfWebRequest $request) {
		$this->coupons = Doctrine::getTable('Coupon')->createQuery('a')->execute();
	}

	public function executeSettingsIndex(sfWebRequest $request) {
		$this->settings = Doctrine::getTable('Settings')->createQuery('a')->execute();
	}


	public function executeTaxIndex(sfWebRequest $request) {
		$this->taxes = Doctrine::getTable('Tax')->createQuery('a')->execute();
	}


	public function executeProductNew(sfWebRequest $request) {
		$this->form = new productForm();
	}

	public function executeCategoryNew(sfWebRequest $request) {
		$this->form = new CategoryForm();

		$this->form->setParent($request->getParameter('parent'));

	}

	public function executeCouponNew(sfWebRequest $request) {
		$this->form = new couponForm();
	}

	public function executeTaxNew(sfWebRequest $request) {
		$this->form = new taxForm();
	}


	public function executeProductCreate(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST));
		$this->form = new productForm();
		$this->processProductForm($request, $this->form);
		$this->setTemplate('productNew');
	}

	public function executeCategoryCreate(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST));
		$this->form = new categoryForm();
		$this->processCategoryForm($request, $this->form);
		$this->setTemplate('categoryNew');
	}

	public function executeCouponCreate(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST));
		$this->form = new couponForm();
		$this->processCouponForm($request, $this->form);
		$this->setTemplate('couponNew');
	}

	public function executeTaxCreate(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST));
		$this->form = new taxForm();
		$this->processTaxForm($request, $this->form);
		$this->setTemplate('taxNew');
	}


	public function executeProductEdit(sfWebRequest $request) {
		$this->forward404Unless($product = Doctrine::getTable('Product')->find($request->getParameter('id')), sprintf('Object product does not exist (%s).', $request->getParameter('id')));
		$this->form = new ProductForm($product);
	}

	public function executeCategoryEdit(sfWebRequest $request) {
		$this->forward404Unless($category = Doctrine::getTable('Category')->find($request->getParameter('id')), sprintf('Object category does not exist (%s).', $request->getParameter('id')));
		$this->form = new CategoryForm($category);
	}

	public function executeCouponEdit(sfWebRequest $request) {
		$this->forward404Unless($coupon = Doctrine::getTable('Coupon')->find($request->getParameter('id')), sprintf('Object coupon does not exist (%s).', $request->getParameter('id')));
		$this->form = new CouponForm($coupon);
	}

	public function executeTaxEdit(sfWebRequest $request) {
		$this->forward404Unless($tax = Doctrine::getTable('Tax')->find($request->getParameter('id')), sprintf('Object tax does not exist (%s).', $request->getParameter('id')));
		$this->form = new TaxForm($tax);
	}


	public function executeProductUpdate(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
		$this->forward404Unless($product = Doctrine::getTable('product')->find(array($request->getParameter('id'))), sprintf('Object product does not exist (%s).', $request->getParameter('id')));
		$this->form = new productForm($product);
		$this->processProductForm($request, $this->form);
		$this->setTemplate('productEdit');
	}

	public function executeProductUpdateBatch(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
		$this->forward404Unless($weights = $request->getParameter('weights'), sprintf('Weights not specified.'));
		$this->forward404Unless($prices = $request->getParameter('prices'), sprintf('Prices not specified.'));
		$this->forward404Unless($quantities = $request->getParameter('quantities'), sprintf('Quantities not specified.'));
		$this->forward404Unless($states = $request->getParameter('states'), sprintf('States not specified.'));
		$this->forward404Unless($saleprices = $request->getParameter('saleprices'), sprintf('Sale Prices not specified.'));
		$this->forward404Unless($codes = $request->getParameter('codes'), sprintf('Codes not specified.'));

		$output = array();

		foreach ($weights as $key => $weight) {
			if (($weights[$key] != null) and ($prices[$key] != null) and ($quantities[$key] != null)) {
				$this->forward404Unless($product = Doctrine::getTable('product')->find($key), sprintf('Object product does not exist (%s).', $key));
				$changed = false;

				if (number_format($product->getWeight(), 2) != number_format($weights[$key], 2)) { $product->setWeight($weights[$key]); $changed = true; }
				if ($product->getQuantity() != $quantities[$key]) { $product->setQuantity($quantities[$key]); $changed = true; }
				if (number_format($product->getSalePrice(),2) != number_format($saleprices[$key], 2)) { $product->setSalePrice($saleprices[$key]); $changed = true; }
				if ($product->getActive() !=  $states[$key]) { $product->setActive($states[$key]); $changed = true; }
				if ($product->getCode() != $codes[$key]) { $product->setCode($codes[$key]); $changed = true;}
				if (number_format($product->getprice(), 2) != number_format($prices[$key],2)) { $product->setPrice($prices[$key]); $changed = true;  }

				if ($changed) {
					$product->save();
					$product->state(3);
					$output[$key] = $product->getId();
				}

			}
		}
		echo json_encode($output);
		 
		return true;
	}

	public function executeCategoryUpdate(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
		$this->forward404Unless($category = Doctrine::getTable('Category')->find(array($request->getParameter('id'))), sprintf('Object category does not exist (%s).', $request->getParameter('id')));
		$this->form = new categoryForm($category);
		$this->processCategoryForm($request, $this->form);
		$this->setTemplate('categoryEdit');
	}

	public function executeCouponUpdate(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
		$this->forward404Unless($coupon = Doctrine::getTable('Coupon')->find(array($request->getParameter('id'))), sprintf('Object coupon does not exist (%s).', $request->getParameter('id')));
		$this->form = new couponForm($coupon);
		$this->processCouponForm($request, $this->form);
		$this->setTemplate('couponEdit');
	}

	public function executeTaxUpdate(sfWebRequest $request) {
		$this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
		$this->forward404Unless($tax = Doctrine::getTable('Tax')->find(array($request->getParameter('id'))), sprintf('Object tax does not exist (%s).', $request->getParameter('id')));
		$this->form = new taxForm($tax);
		$this->processTaxForm($request, $this->form);
		$this->setTemplate('taxEdit');
	}


	public function executeProductDelete(sfWebRequest $request) {
		$request->checkCSRFProtection();
		$this->forward404Unless($product = Doctrine::getTable('product')->find(array($request->getParameter('id'))), sprintf('Object product does not exist (%s).', $request->getParameter('id')));
		$product->delete();
		$this->redirect('cartadmin/productIndex');
	}

	public function executeCategoryDelete(sfWebRequest $request) {
		$request->checkCSRFProtection();
		$this->forward404Unless($category = Doctrine_Core::getTable('Category')->findOneById($request->getParameter('id')), sprintf('Object category does not exist (%s).', $request->getParameter('id')));
		$category->getNode()->delete();
		$this->redirect('cartadmin/categoryIndex');
	}

	public function executeCouponDelete(sfWebRequest $request) {
		$request->checkCSRFProtection();
		$this->forward404Unless($coupon = Doctrine::getTable('Coupon')->find(array($request->getParameter('id'))), sprintf('Object coupon does not exist (%s).', $request->getParameter('id')));
		$coupon->delete();
		$this->redirect('cartadmin/couponIndex');
	}

	public function executeTaxDelete(sfWebRequest $request) {
		$request->checkCSRFProtection();
		$this->forward404Unless($tax = Doctrine::getTable('Tax')->find(array($request->getParameter('id'))), sprintf('Object tax does not exist (%s).', $request->getParameter('id')));
		$tax->delete();
		$this->redirect('cartadmin/taxIndex');
	}


	protected function processProductForm(sfWebRequest $request, sfForm $form) {
		$form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
		if ($form->isValid())
		{
			$product = $form->save();
			$this->redirect('cartadmin/productEdit?id='.$product->getId());
		}
	}

	protected function processCategoryForm(sfWebRequest $request, sfForm $form) {
		$form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
		if ($form->isValid())
		{
			$category = $form->save();
			$this->redirect('cartadmin/categoryIndex');
		}
	}

	protected function processCouponForm(sfWebRequest $request, sfForm $form) {
		$form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
		if ($form->isValid())
		{
			$coupon = $form->save();
			$this->redirect('cartadmin/couponEdit?id='.$coupon->getId());
		}
	}

	protected function processTaxForm(sfWebRequest $request, sfForm $form) {
		$form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
		if ($form->isValid())
		{
			$tax = $form->save();
			$this->redirect('cartadmin/taxEdit?id='.$tax->getId());
		}
	}


}
